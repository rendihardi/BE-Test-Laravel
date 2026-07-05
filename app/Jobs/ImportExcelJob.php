<?php

namespace App\Jobs;

use App\Models\ExcelJob;
use App\Imports\ProductImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobRecordId;
    protected $tempFilePath;
    protected $disk;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobRecordId, string $tempFilePath, string $disk = 'public')
    {
        $this->jobRecordId = $jobRecordId;
        $this->tempFilePath = $tempFilePath;
        $this->disk = $disk;
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

        // Enable console auditing dynamically for the duration of this queue job
        config(['audit.console' => true]);

        // Login the user who triggered the import to ensure audits are correctly mapped
        $user = \App\Models\User::find($jobRecord->user_id);
        if ($user) {
            \Illuminate\Support\Facades\Auth::setUser($user);
            \Illuminate\Support\Facades\Auth::guard('sanctum')->setUser($user);
            \Illuminate\Support\Facades\Auth::guard('api')->setUser($user);
            \Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
        }

        $jobRecord->update(['status' => 'processing']);

        try {
            // Check if file exists
            if (!Storage::disk($this->disk)->exists($this->tempFilePath)) {
                throw new \Exception("Uploaded import file does not exist in storage: {$this->tempFilePath}");
            }

            // Run import using phpspreadsheet via Laravel Excel
            $filePathOnDisk = Storage::disk($this->disk)->path($this->tempFilePath);
            Excel::import(new ProductImport, $filePathOnDisk);

            $jobRecord->update([
                'status' => 'completed',
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
