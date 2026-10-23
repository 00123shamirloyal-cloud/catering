<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\FollowUpTask;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateInquiry
{
    public function __construct(private RecordAuditEntry $auditEntry) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Inquiry
    {
        $existing = Inquiry::query()->where('idempotency_token', $data['idempotency_token'])->first();

        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data): Inquiry {
                $normalizedName = Str::lower(trim($data['full_name']));
                $normalizedEmail = Str::lower(trim($data['email']));
                $normalizedPhone = preg_replace('/[^0-9+]/', '', $data['phone']) ?? '';
                $assignee = User::query()->where('role', 'owner')->orderBy('id')->first();

                $customer = Customer::query()
                    ->whereRaw('LOWER(name) = ?', [$normalizedName])
                    ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                    ->where('phone', $normalizedPhone)
                    ->first();

                if (! $customer) {
                    $customer = Customer::query()->create([
                        'name' => trim($data['full_name']),
                        'email' => $normalizedEmail,
                        'phone' => $normalizedPhone,
                        'preferred_contact' => $data['preferred_contact'],
                        'lead_source' => 'website',
                        'assigned_to' => $assignee?->id,
                    ]);
                }

                $inquiry = Inquiry::query()->create([
                    'reference' => 'INQ-'.Str::upper(Str::random(10)),
                    'idempotency_token' => $data['idempotency_token'],
                    'customer_id' => $customer->id,
                    'customer_name' => trim($data['full_name']),
                    'customer_email' => $normalizedEmail,
                    'customer_phone' => $normalizedPhone,
                    'preferred_contact' => $data['preferred_contact'],
                    'event_type' => $data['event_type'],
                    'event_date' => $data['event_date'],
                    'alternative_date' => $data['alternative_date'] ?? null,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'guest_count' => $data['guest_count'],
                    'city' => $data['city'],
                    'venue' => $data['venue'] ?? null,
                    'setting' => $data['setting'] ?? null,
                    'budget_range' => $data['budget_range'] ?? null,
                    'package_id' => $data['package_id'] ?? null,
                    'dietary_preferences' => $data['dietary_preferences'] ?? null,
                    'requirements' => $data['requirements'] ?? null,
                    'message' => $data['message'] ?? null,
                    'privacy_accepted' => true,
                    'status' => 'new',
                    'assigned_to' => $assignee?->id,
                    'next_follow_up_at' => now()->addDay(),
                    'submitted_at' => now(),
                ]);

                $selectionIds = collect($data['service_ids'] ?? [])
                    ->mapWithKeys(fn (int|string $id): array => [(int) $id => ['selection_type' => 'service']]);

                if ($inquiry->package_id) {
                    $selectionIds->put($inquiry->package_id, ['selection_type' => 'package']);
                }

                if ($selectionIds->isNotEmpty()) {
                    $inquiry->serviceSelections()->attach($selectionIds->all());
                }

                FollowUpTask::query()->create([
                    'title' => 'Follow up with '.$customer->name,
                    'description' => 'Respond to inquiry '.$inquiry->reference,
                    'inquiry_id' => $inquiry->id,
                    'customer_id' => $customer->id,
                    'assigned_to' => $assignee?->id,
                    'due_at' => now()->addDay(),
                    'priority' => 'normal',
                    'status' => 'open',
                ]);

                $this->auditEntry->handle($inquiry, 'Inquiry received from the public website', [
                    'reference' => $inquiry->reference,
                ]);

                return $inquiry;
            });
        } catch (QueryException $exception) {
            $existing = Inquiry::query()->where('idempotency_token', $data['idempotency_token'])->first();

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }
}
