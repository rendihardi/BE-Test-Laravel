<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductResource;
use App\Interface\ProductInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private ProductInterface $productRepository;

    public function __construct(ProductInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['category_id', 'is_active', 'min_price', 'max_price']);
            $products = $this->productRepository->getAll(
                $request->search,
                $filters,
                $request->sort_by,
                $request->sort_order,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Data Product', ProductResource::collection($products), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function getAllPaginated(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer',
            'category_id' => 'nullable|uuid',
            'is_active' => 'nullable|string',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'sort_by' => 'nullable|string',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
        ]);

        try {
            $filters = $request->only(['category_id', 'is_active', 'min_price', 'max_price']);
            $products = $this->productRepository->getAllPaginated(
                $request->search,
                $filters,
                $request->sort_by,
                $request->sort_order,
                $request->row_per_page
            );

            return ResponseHelper::jsonResponse(true, 'Data Product', PaginatedResource::make($products, ProductResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();
        try {
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products/images', 'public');
            }
            if ($request->hasFile('specification_pdf')) {
                $validated['specification_pdf'] = $request->file('specification_pdf')->store('products/pdfs', 'public');
            }

            $product = $this->productRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Data Product Created', new ProductResource($product), 201);
        } catch (\Throwable $e) {
            // Delete files if DB transaction fails
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }
            if (isset($validated['specification_pdf'])) {
                Storage::disk('public')->delete($validated['specification_pdf']);
            }
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = $this->productRepository->getById($id);
            if (!$product) {
                return ResponseHelper::jsonResponse(false, 'Data Product Not Found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Data Product', new ProductResource($product), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        try {
            $product = $this->productRepository->getById($id);
            if (!$product) {
                return ResponseHelper::jsonResponse(false, 'Data Product Not Found', null, 404);
            }

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products/images', 'public');
            }

            if ($request->hasFile('specification_pdf')) {
                if ($product->specification_pdf) {
                    Storage::disk('public')->delete($product->specification_pdf);
                }
                $validated['specification_pdf'] = $request->file('specification_pdf')->store('products/pdfs', 'public');
            }

            $product = $this->productRepository->update($validated, $id);

            return ResponseHelper::jsonResponse(true, 'Data Product Updated', new ProductResource($product), 200);
        } catch (\Throwable $e) {
            // Clean up newly uploaded files if update fails
            if ($request->hasFile('image') && isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }
            if ($request->hasFile('specification_pdf') && isset($validated['specification_pdf'])) {
                Storage::disk('public')->delete($validated['specification_pdf']);
            }
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = $this->productRepository->getById($id);
            if (!$product) {
                return ResponseHelper::jsonResponse(false, 'Data Product Not Found', null, 404);
            }

            // We do not delete files on soft delete so we can restore if needed.
            $this->productRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Data Product Deleted', null, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
