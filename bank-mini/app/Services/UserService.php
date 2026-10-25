<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers(?string $search = null): Collection
    {
        $query = User::orderBy('name', 'asc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => ! empty($data['email']) ? trim($data['email']) : null,
            'email_verified_at' => ! empty($data['email']) ? now() : null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public function updateUser(User $user, array $data): bool
    {
        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'],
            'role' => $data['role'],
            'status' => $data['status'] ?? $user->status,
        ];

        if (array_key_exists('email', $data)) {
            $newEmail = ! empty($data['email']) ? trim($data['email']) : null;
            $updateData['email'] = $newEmail;
            if ($newEmail && $newEmail !== $user->email) {
                $updateData['email_verified_at'] = now();
            } elseif (! $newEmail) {
                $updateData['email_verified_at'] = null;
            }
        }

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $user->update($updateData);
    }

    public function toggleStatus(User $user): bool
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        return $user->update(['status' => $newStatus]);
    }
}
