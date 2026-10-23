<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateOwnerAccount extends Command
{
    protected $signature = 'staff:create-owner';

    protected $description = 'Securely create the first active owner account';

    public function handle(): int
    {
        if (User::query()->where('role', 'owner')->where('is_active', true)->exists()) {
            $this->error('An active owner already exists. Use Staff in the dashboard to add or change accounts.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Owner name'));
        $email = mb_strtolower(trim((string) $this->ask('Owner email')));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if ($name === '' || mb_strlen($name) > 120) {
            $this->error('Enter a name between 1 and 120 characters.');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::query()->where('email', $email)->exists()) {
            $this->error('Enter a valid email address that is not already in use.');

            return self::FAILURE;
        }

        if (mb_strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->error('The password must contain at least 12 characters and match its confirmation.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->info('Active owner account created. Sign in at /staff/sign-in.');

        return self::SUCCESS;
    }
}
