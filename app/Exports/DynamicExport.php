<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DynamicExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;
    protected $columns;
    protected $columnMappings;

    public function __construct($collection, array $columns, array $columnMappings)
    {
        $this->collection = $collection;
        $this->columns = $columns;
        $this->columnMappings = $columnMappings;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return array_map(function ($col) {
            return $this->columnMappings[$col]['label'] ?? ucwords(str_replace('_', ' ', $col));
        }, $this->columns);
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->columns as $col) {
            if (isset($this->columnMappings[$col]['value'])) {
                $mapped[] = $this->columnMappings[$col]['value']($row);
            } else {
                $mapped[] = $row->{$col} ?? '';
            }
        }
        return $mapped;
    }
}
