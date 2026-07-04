<?php

namespace App\Interface;

interface UserInterface
{
    public function getAll(
        ?string $search,
        ?int $limit,
        bool $execute = false,
    );

    public function getAllPaginated(
        ?string $search,
        ?int $rowPerPage,
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
