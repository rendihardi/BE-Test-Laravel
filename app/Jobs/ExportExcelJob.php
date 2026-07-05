<?php

namespace App\Jobs;

use App\Models\ExcelJob;
use App\Exports\DynamicExport;
use App\Repositories\ProductRepository;
use App\Repositories\StockTransactionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobRecordId;
    protected $module;
    protected $filters;
    protected $search;
    protected $sortBy;
    protected $sortOrder;
    protected $columns;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $jobRecordId,
        string $module,
        array $filters = [],
        ?string $search = null,
        ?string $sortBy = null,
        ?string $sortOrder = null,
        array $columns = []
    ) {
        $this->jobRecordId = $jobRecordId;
        $this->module = $module;
        $this->filters = $filters;
        $this->search = $search;
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
        $this->columns = $columns;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $jobRecord = ExcelJob::find($this->jobRecordId);
        if (!$jobRecord) {
            return;
        }

        $jobRecord->update(['status' => 'processing']);

        try {
            $fileName = $this->module . '_export_' . Str::random(10) . '_' . time() . '.xlsx';
            $filePath = 'exports/' . $fileName;

            if ($this->module === 'product') {
                $repository = app(ProductRepository::class);
                $collection = $repository->getAll(
                    $this->search,
                    $this->filters,
                    $this->sortBy,
                    $this->sortOrder,
                    null,
                    true
                );

                $columnMappings = [
                    'id' => [
                        'label' => 'ID',
                        'value' => fn($row) => $row->id,
                    ],
                    'product_code' => [
                        'label' => 'Product Code',
                        'value' => fn($row) => $row->product_code,
                    ],
                    'product_name' => [
                        'label' => 'Product Name',
                        'value' => fn($row) => $row->product_name,
                    ],
                    'price' => [
                        'label' => 'Price',
                        'value' => fn($row) => $row->price,
                    ],
                    'current_stock' => [
                        'label' => 'Current Stock',
                        'value' => fn($row) => $row->current_stock,
                    ],
                    'stock_status' => [
                        'label' => 'Stock Status',
                        'value' => fn($row) => $row->stock_status,
                    ],
                    'category_name' => [
                        'label' => 'Category Name',
                        'value' => fn($row) => $row->category?->name,
                    ],
                    'is_active' => [
                        'label' => 'Is Active',
                        'value' => fn($row) => $row->is_active ? 'Yes' : 'No',
                    ],
                    'created_at' => [
                        'label' => 'Created At',
                        'value' => fn($row) => $row->created_at ? $row->created_at->toIso8601String() : '',
                    ]
                ];

                // Defaults if none provided
                $selectedColumns = count($this->columns) > 0 ? $this->columns : ['product_code', 'product_name', 'category_name', 'price', 'current_stock', 'stock_status', 'is_active'];
            } else {
                // stockin, stockout, adjustment modules
                $repository = app(StockTransactionRepository::class);
                
                // Map modules to transaction types
                $type = null;
                if ($this->module === 'stockin') {
                    $type = 'IN';
                } elseif ($this->module === 'stockout') {
                    $type = 'OUT';
                } elseif ($this->module === 'adjustment') {
                    $type = 'ADJUSTMTENT'; // exact enum spelling
                }

                $filters = $this->filters;
                if ($type !== null) {
                    $filters['type'] = $type;
                }

                $collection = $repository->getAll(
                    $this->search,
                    $filters,
                    $this->sortBy,
                    $this->sortOrder,
                    null,
                    true
                );

                $columnMappings = [
                    'id' => [
                        'label' => 'ID',
                        'value' => fn($row) => $row->id,
                    ],
                    'trx_code' => [
                        'label' => 'Transaction Code',
                        'value' => fn($row) => $row->trx_code,
                    ],
                    'transaction_date' => [
                        'label' => 'Transaction Date',
                        'value' => fn($row) => $row->transaction_date,
                    ],
                    'type' => [
                        'label' => 'Type',
                        'value' => fn($row) => $row->type,
                    ],
                    'qty' => [
                        'label' => 'Quantity',
                        'value' => fn($row) => $row->qty,
                    ],
                    'stock_before' => [
                        'label' => 'Stock Before',
                        'value' => fn($row) => $row->stock_before,
                    ],
                    'stock_after' => [
                        'label' => 'Stock After',
                        'value' => fn($row) => $row->stock_after,
                    ],
                    'product_name' => [
                        'label' => 'Product Name',
                        'value' => fn($row) => $row->product_name,
                    ],
                    'category_name' => [
                        'label' => 'Category Name',
                        'value' => fn($row) => $row->category_name,
                    ],
                    'price' => [
                        'label' => 'Price',
                        'value' => fn($row) => $row->price,
                    ],
                    'remarks' => [
                        'label' => 'Remarks',
                        'value' => fn($row) => $row->remarks,
                    ],
                    'created_by' => [
                        'label' => 'Created By',
                        'value' => fn($row) => $row->creator?->name,
                    ],
                    'created_at' => [
                        'label' => 'Created At',
                        'value' => fn($row) => $row->created_at ? $row->created_at->toIso8601String() : '',
                    ]
                ];

                // Defaults if none provided
                $selectedColumns = count($this->columns) > 0 ? $this->columns : ['trx_code', 'transaction_date', 'type', 'product_name', 'qty', 'stock_before', 'stock_after', 'created_by'];
            }

            // Ensure all selected columns exist in our mappings
            $selectedColumns = array_intersect($selectedColumns, array_keys($columnMappings));
            if (empty($selectedColumns)) {
                $selectedColumns = array_keys($columnMappings);
            }

            $exportInstance = new DynamicExport($collection, $selectedColumns, $columnMappings);

            // Store Excel to public disk
            Excel::store($exportInstance, $filePath, 'public');

            $jobRecord->update([
                'status' => 'completed',
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            $jobRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $jobRecord = ExcelJob::find($this->jobRecordId);
        if ($jobRecord) {
            $jobRecord->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
