<?php

namespace App\Repositories;

use App\Interface\RoleInterface;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleInterface
{
    public function getAll(
        ?string $search,
        ?int $limit,
        ?bool $execute = false,
    ) {
        $query = Role::query()->with('permissions');

        if ($search) {
            $query->search($search);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $execute ? $query->get() : $query;
    }

    public function getAllPaginated(
        ?string $search,
        ?int $rowPerPage
    ) {
        $query = $this->getAll($search, null, false);
        $perPage = $rowPerPage ?? 15;
        return $query->paginate($perPage);
    }

    public function getById(?string $id): ?Role
    {
        return Role::where('id', $id)->with('permissions')->first();
    }

    public function create(array $data): Role
    {
        DB::beginTransaction();
        try {
            $roleData = [
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'sanctum',
            ];

            $role = Role::create($roleData);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();
            return $role;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, ?string $id): ?Role
    {
        DB::beginTransaction();
        try {
            $role = $this->getById($id);
            if (!$role) {
                DB::rollBack();
                return null;
            }

            $roleData = [];
            if (isset($data['name'])) {
                $roleData['name'] = $data['name'];
            }
            if (isset($data['guard_name'])) {
                $roleData['guard_name'] = $data['guard_name'];
            }

            $role->update($roleData);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();
            return $role;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(?string $id): bool
    {
        DB::beginTransaction();
        try {
            $role = $this->getById($id);
            if (!$role) {
                DB::rollBack();
                return false;
            }

            $deleted = $role->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
