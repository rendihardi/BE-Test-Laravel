<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PaginatedResource;
use App\Interface\CategoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private CategoryInterface $categoryRepository;

    public function __construct(CategoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $categories = $this->categoryRepository->getAll(
                $request->search,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Data Category', CategoryResource::collection($categories), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function getAllPaginated(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer',
        ]);

        try {
            $categories = $this->categoryRepository->getAllPaginated(
                $request->search,
                $request->row_per_page
            );

            return ResponseHelper::jsonResponse(true, 'Data Category', PaginatedResource::make($categories, CategoryResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        $validated = $request->validated();
        try {
            $category = $this->categoryRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Data Category Created', new CategoryResource($category), 201);
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
            $category = $this->categoryRepository->getById($id);
            if (!$category) {
                return ResponseHelper::jsonResponse(false, 'Data Category Not Found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Data Category', new CategoryResource($category), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        try {
            $category = $this->categoryRepository->getById($id);
            if (!$category) {
                return ResponseHelper::jsonResponse(false, 'Data Category Not Found', null, 404);
            }

            $category = $this->categoryRepository->update($validated, $id);

            return ResponseHelper::jsonResponse(true, 'Data Category Updated', new CategoryResource($category), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = $this->categoryRepository->getById($id);
            if (!$category) {
                return ResponseHelper::jsonResponse(false, 'Data Category Not Found', null, 404);
            }

            $this->categoryRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Data Category Deleted', null, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
