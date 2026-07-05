<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StockTransactionStoreRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\StockTransactionResource;
use App\Interface\StockTransactionInterface;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    private StockTransactionInterface $stockTransactionRepository;

    public function __construct(StockTransactionInterface $stockTransactionRepository)
    {
        $this->stockTransactionRepository = $stockTransactionRepository;
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'limit' => 'nullable|integer|min:1',
            'product_id' => 'nullable|uuid',
            'type' => 'nullable|string|in:IN,OUT,ADJUSTMTENT',
            'created_by' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ]);

        try {
            $filters = $request->only(['product_id', 'type', 'created_by', 'start_date', 'end_date']);
            $transactions = $this->stockTransactionRepository->getAll(
                $request->search,
                $filters,
                $request->sort_by,
                $request->sort_order,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Data Stock Transaction', StockTransactionResource::collection($transactions), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function getAllPaginated(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer',
            'product_id' => 'nullable|uuid',
            'type' => 'nullable|string|in:IN,OUT,ADJUSTMTENT',
            'created_by' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ]);

        try {
            $filters = $request->only(['product_id', 'type', 'created_by', 'start_date', 'end_date']);
            $transactions = $this->stockTransactionRepository->getAllPaginated(
                $request->search,
                $filters,
                $request->sort_by,
                $request->sort_order,
                $request->row_per_page
            );

            return ResponseHelper::jsonResponse(true, 'Data Stock Transaction', PaginatedResource::make($transactions, StockTransactionResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockTransactionStoreRequest $request)
    {
        $validated = $request->validated();
        try {
            $transaction = $this->stockTransactionRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Data Stock Transaction Created', new StockTransactionResource($transaction), 201);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $transaction = $this->stockTransactionRepository->getById($id);
            if (!$transaction) {
                return ResponseHelper::jsonResponse(false, 'Data Stock Transaction Not Found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Data Stock Transaction', new StockTransactionResource($transaction), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Perform Stock In.
     */
    public function stockIn(Request $request, string $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1',
            'reference_document' => 'nullable|file|mimes:pdf|min:100|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            if ($request->hasFile('reference_document')) {
                $validated['reference_document'] = $request->file('reference_document')->store('transactions/documents', 'public');
            }

            $validated['product_id'] = $id;
            $validated['type'] = 'IN';

            $transaction = $this->stockTransactionRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Stock In Success', new StockTransactionResource($transaction), 201);
        } catch (\Throwable $e) {
            if (isset($validated['reference_document'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($validated['reference_document']);
            }
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Perform Stock Out.
     */
    public function stockOut(Request $request, string $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1',
            'reference_document' => 'nullable|file|mimes:pdf|min:100|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            if ($request->hasFile('reference_document')) {
                $validated['reference_document'] = $request->file('reference_document')->store('transactions/documents', 'public');
            }

            $validated['product_id'] = $id;
            $validated['type'] = 'OUT';

            $transaction = $this->stockTransactionRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Stock Out Success', new StockTransactionResource($transaction), 201);
        } catch (\Throwable $e) {
            if (isset($validated['reference_document'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($validated['reference_document']);
            }
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Perform Stock Adjustment.
     */
    public function adjustStock(Request $request, string $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:0',
            'reference_document' => 'nullable|file|mimes:pdf|min:100|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            if ($request->hasFile('reference_document')) {
                $validated['reference_document'] = $request->file('reference_document')->store('transactions/documents', 'public');
            }

            $validated['product_id'] = $id;
            $validated['type'] = 'ADJUSTMTENT';

            $transaction = $this->stockTransactionRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Stock Adjustment Success', new StockTransactionResource($transaction), 201);
        } catch (\Throwable $e) {
            if (isset($validated['reference_document'])) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($validated['reference_document']);
            }
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
