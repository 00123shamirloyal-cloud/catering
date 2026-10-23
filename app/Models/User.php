<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isStaff(): bool
    {
        return $this->is_active && in_array($this->role, ['owner', 'manager', 'sales', 'operations', 'finance', 'content_editor'], true);
    }

    public function canViewCrm(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'sales', 'operations', 'finance'], true);
    }

    public function canManageCustomers(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'sales'], true);
    }

    public function canManageWebsite(): bool
    {
        return in_array($this->role, ['owner', 'content_editor'], true);
    }

    public function canPublishWebsite(): bool
    {
        return $this->role === 'owner';
    }

    public function canManageFinance(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'sales', 'finance'], true);
    }

    public function canManagePayments(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'finance'], true);
    }

    public function canManageEvents(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'operations'], true);
    }

    public function canManageAppointments(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'sales', 'operations'], true);
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'assigned_to');
    }

    public function managedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'event_manager_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
