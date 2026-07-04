<?php

namespace App\Interface;

interface ProductInterface
{
    public function getAll(
        ?string $search,
        array $filters,
        ?string $sortBy,
        ?string $sortOrder,
        ?int $limit,
        ?bool $execute = false
    );

    public function getAllPaginated(
        ?string $search,
        array $filters,
        ?string $sortBy,
        ?string $sortOrder,
        ?int $rowPerPage
    );

    public function getById(
        ?string $id
    );

    public function create(
        array $data
    );

    public function update(
        array $data,
        ?string $id
    );

    public function delete(
        ?string $id
    );
}
