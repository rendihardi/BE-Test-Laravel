<?php

namespace App\Http\Controllers;

use App\Models\ExcelJob;
use App\Jobs\ExportExcelJob;
use App\Jobs\ImportExcelJob;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExcelController extends Controller
{
    /**
     * Trigger asynchronous Excel export for products.
     */
    public function exportProducts(Request $request)
    {
        $request->validate([
            'columns' => 'nullable|array',
            'columns.*' => 'string',
            'search' => 'nullable|string',
            'category_id' => 'nullable|uuid',
            'stock_status' => 'nullable|string|in:in stock,low stock,out of stock',
            'is_active' => 'nullable|string|in:true,false',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|string|in:product_name,product_code,price,current_stock,created_at',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ]);

        try {
            $filters = $request->only(['category_id', 'stock_status', 'is_active', 'min_price', 'max_price']);
            return $this->queueExport('product', $request, $filters);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Trigger asynchronous Excel export for stock in transactions.
     */
    public function exportStockIn(Request $request)
    {
        return $this->exportStockTransaction('stockin', $request);
    }

    /**
     * Trigger asynchronous Excel export for stock out transactions.
     */
    public function exportStockOut(Request $request)
    {
        return $this->exportStockTransaction('stockout', $request);
    }

    /**
     * Trigger asynchronous Excel export for stock adjustment transactions.
     */
    public function exportAdjustments(Request $request)
    {
        return $this->exportStockTransaction('adjustment', $request);
    }

    /**
     * Trigger asynchronous Excel export for all stock transactions.
     */
    public function exportStockTransactions(Request $request)
    {
        return $this->exportStockTransaction('stock-transactions', $request);
    }

    /**
     * Helper to validate and queue stock transaction exports.
     */
    protected function exportStockTransaction(string $module, Request $request)
    {
        $request->validate([
            'columns' => 'nullable|array',
            'columns.*' => 'string',
            'search' => 'nullable|string',
            'product_id' => 'nullable|uuid',
            'created_by' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ]);

        try {
            $filters = $request->only(['product_id', 'created_by', 'start_date', 'end_date']);
            return $this->queueExport($module, $request, $filters);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Helper method to initialize and dispatch background export job.
     */
    protected function queueExport(string $module, Request $request, array $filters)
    {
        $user = Auth::user();

        // Create Excel Job tracker
        $jobRecord = ExcelJob::create([
            'type' => 'export',
            'module' => $module,
            'status' => 'pending',
            'user_id' => $user->uuid,
        ]);

        // Dispatch to background queue
        ExportExcelJob::dispatch(
            $jobRecord->id,
            $module,
            $filters,
            $request->search,
            $request->sort_by,
            $request->sort_order,
            $request->columns ?? []
        );

        return ResponseHelper::jsonResponse(true, 'Export task queued successfully', [
            'job_id' => $jobRecord->id,
            'status' => $jobRecord->status,
            'module' => $jobRecord->module,
            'type' => $jobRecord->type,
        ], 202); // 202 Accepted
    }

    /**
     * Trigger asynchronous Excel product import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        try {
            $user = Auth::user();
            $file = $request->file('file');

            // Save the uploaded file temporarily
            $fileName = 'imports/' . Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('', $file, $fileName);

            // Create Excel Job tracker
            $jobRecord = ExcelJob::create([
                'type' => 'import',
                'module' => 'product',
                'status' => 'pending',
                'file_path' => $fileName,
                'user_id' => $user->uuid,
            ]);

            // Dispatch to background queue
            ImportExcelJob::dispatch(
                $jobRecord->id,
                $fileName,
                'public'
            );

            return ResponseHelper::jsonResponse(true, 'Import task queued successfully', [
                'job_id' => $jobRecord->id,
                'status' => $jobRecord->status,
                'module' => $jobRecord->module,
                'type' => $jobRecord->type,
            ], 202); // 202 Accepted
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * List all excel tasks for the authenticated user.
     */
    public function listJobs()
    {
        try {
            $user = Auth::user();
            $jobs = ExcelJob::where('user_id', $user->uuid)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $mappedJobs = $jobs->getCollection()->map(function ($job) {
                $downloadUrl = null;
                if ($job->type === 'export' && $job->status === 'completed' && $job->file_path) {
                    $downloadUrl = asset('storage/' . $job->file_path);
                }

                return [
                    'id' => $job->id,
                    'type' => $job->type,
                    'module' => $job->module,
                    'status' => $job->status,
                    'download_url' => $downloadUrl,
                    'error_message' => $job->error_message,
                    'created_at' => $job->created_at->toIso8601String(),
                    'updated_at' => $job->updated_at->toIso8601String(),
                ];
            });

            // Reconstruct pagination format to match system resource
            return response()->json([
                'success' => true,
                'message' => 'Excel Jobs List',
                'data' => [
                    'data' => $mappedJobs,
                    'meta' => [
                        'current_page' => $jobs->currentPage(),
                        'from' => $jobs->firstItem(),
                        'last_page' => $jobs->lastPage(),
                        'path' => request()->url(),
                        'per_page' => $jobs->perPage(),
                        'to' => $jobs->lastItem(),
                        'total' => $jobs->total(),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Check status and retrieve download URL/error message of a specific excel task.
     */
    public function showJob($id)
    {
        try {
            $user = Auth::user();
            $job = ExcelJob::where('user_id', $user->uuid)->where('id', $id)->first();

            if (!$job) {
                return ResponseHelper::jsonResponse(false, 'Job not found.', null, 404);
            }

            $downloadUrl = null;
            if ($job->type === 'export' && $job->status === 'completed' && $job->file_path) {
                $downloadUrl = asset('storage/' . $job->file_path);
            }

            return ResponseHelper::jsonResponse(true, 'Excel Job Detail', [
                'id' => $job->id,
                'type' => $job->type,
                'module' => $job->module,
                'status' => $job->status,
                'download_url' => $downloadUrl,
                'error_message' => $job->error_message,
                'created_at' => $job->created_at->toIso8601String(),
                'updated_at' => $job->updated_at->toIso8601String(),
            ], 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Download product import template.
     */
    public function downloadProductTemplate(Request $request)
    {
        try {
            $format = strtolower($request->query('format', 'xlsx'));
            $extension = $format === 'csv' ? 'csv' : 'xlsx';
            $fileName = 'product_import_template.' . $extension;
            $filePath = public_path('templates/' . $fileName);

            if (!file_exists($filePath)) {
                return ResponseHelper::jsonResponse(false, 'Template not found.', null, 404);
            }

            return response()->download($filePath, $fileName, [
                'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
