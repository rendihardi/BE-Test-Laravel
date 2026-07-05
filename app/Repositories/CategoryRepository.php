<?php

namespace App\Repositories;

use App\Interface\CategoryInterface;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryInterface
{
    public function getAll(
        ?string $search,
        ?int $limit,
        ?bool $execute = false,
    ) {
        $query = Category::query();

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
        $perPage = $rowPerPage ?? 10;
        return $query->paginate($perPage);
    }

    public function getById(?string $id): ?Category
    {
        return Category::where('id', $id)->first();
    }

    public function create(array $data): Category
    {
        DB::beginTransaction();
        try {
            $category = Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return $category;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, ?string $id): ?Category
    {
        DB::beginTransaction();
        try {
            $category = $this->getById($id);
            if (!$category) {
                DB::rollBack();
                return null;
            }

            $category->update([
                'name' => $data['name'] ?? $category->name,
                'description' => array_key_exists('description', $data) ? $data['description'] : $category->description,
                'is_active' => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $category->is_active,
            ]);

            DB::commit();
            return $category;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(?string $id): bool
    {
        DB::beginTransaction();
        try {
            $category = $this->getById($id);
            if (!$category) {
                DB::rollBack();
                return false;
            }

            $deleted = $category->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
