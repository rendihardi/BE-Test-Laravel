<?php

namespace App\Repositories;

use App\Interface\UserInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserInterface
{
    public function getAll(
        ?string $search,
        ?int $limit,
        ?bool $execute = false,
    ) {
        $query = User::query()->with('roles')->search($search);

        if ($limit) {
            $query->limit($limit);
        }

        if ($execute) {
            return $query->get();
        }
        return $query;
    }

    public function getAllPaginated(
        ?string $search,
        ?int $rowPerPage,
        bool $execute = false,
    ) {
        $query = User::query()->with('roles')->search($search);

        $perPage = $rowPerPage ?? 15;

        return $execute ? $query : $query->paginate($perPage);
    }

    public function getById(?string $id): ?User
    {
        return User::where('uuid', $id)->first();
    }

    public function create(array $data): User
    {
        DB::beginTransaction();
        try {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ];

            $user = User::create($userData);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            DB::commit();
            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, ?string $id): ?User
    {
        DB::beginTransaction();
        try {
            $user = $this->getById($id);
            if (!$user) {
                DB::rollBack();
                return null;
            }

            $userData = [];
            if (isset($data['name'])) {
                $userData['name'] = $data['name'];
            }
            if (isset($data['email'])) {
                $userData['email'] = $data['email'];
            }
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $user->update($userData);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            DB::commit();
            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(?string $id): bool
    {
        DB::beginTransaction();
        try {
            $user = $this->getById($id);
            if (!$user) {
                DB::rollBack();
                return false;
            }

            $deleted = $user->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
