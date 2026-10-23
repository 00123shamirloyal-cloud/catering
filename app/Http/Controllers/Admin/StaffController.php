<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordAuditEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = User::query()
            ->whereIn('role', ['owner', 'manager', 'sales', 'operations', 'finance', 'content_editor'])
            ->when($request->string('role')->isNotEmpty(), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->when($request->string('status')->toString() === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->string('status')->toString() === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('admin.staff.form', ['staffMember' => null]);
    }

    public function store(SaveStaffUserRequest $request, RecordAuditEntry $auditEntry): RedirectResponse
    {
        $data = $request->safe()->except(['password_confirmation']);
        $data['is_active'] = true;
        $staffMember = User::query()->create($data);
        $auditEntry->handle($staffMember, 'Staff account created', ['role' => $staffMember->role]);

        return to_route('admin.staff.index')->with('success', 'Staff account created.');
    }

    public function edit(User $staff): View
    {
        abort_unless(in_array($staff->role, ['owner', 'manager', 'sales', 'operations', 'finance', 'content_editor'], true), 404);

        return view('admin.staff.form', ['staffMember' => $staff]);
    }

    public function update(SaveStaffUserRequest $request, User $staff, RecordAuditEntry $auditEntry): RedirectResponse
    {
        abort_unless(in_array($staff->role, ['owner', 'manager', 'sales', 'operations', 'finance', 'content_editor'], true), 404);
        $data = $request->safe()->except(['password_confirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($staff->is($request->user()) && (! ($data['is_active'] ?? true) || ($data['role'] ?? $staff->role) !== 'owner')) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate or remove owner access from your own account.']);
        }

        if ($staff->role === 'owner' && (! ($data['is_active'] ?? true) || ($data['role'] ?? $staff->role) !== 'owner')) {
            $activeOwners = User::query()->where('role', 'owner')->where('is_active', true)->count();

            if ($activeOwners <= 1) {
                throw ValidationException::withMessages(['role' => 'At least one active owner account must remain.']);
            }
        }

        $previousRole = $staff->role;
        $staff->update($data);
        $auditEntry->handle($staff, 'Staff account updated', [
            'role_from' => $previousRole,
            'role_to' => $staff->role,
            'is_active' => $staff->is_active,
        ]);

        return to_route('admin.staff.index')->with('success', 'Staff account updated.');
    }
}
