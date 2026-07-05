<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\RoleResource;
use App\Interface\RoleInterface;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleInterface $roleRepository;

    public function __construct(RoleInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'limit' => 'nullable|integer|min:1',
        ]);

        try {
            $roles = $this->roleRepository->getAll(
                $request->search,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Data Role', RoleResource::collection($roles), 200);
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
            $roles = $this->roleRepository->getAllPaginated(
                $request->search,
                $request->row_per_page
            );

            return ResponseHelper::jsonResponse(true, 'Data Role', PaginatedResource::make($roles, RoleResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreRequest $request)
    {
        $validated = $request->validated();
        try {
            $role = $this->roleRepository->create($validated);

            return ResponseHelper::jsonResponse(true, 'Data Role Created', new RoleResource($role), 201);
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
            $role = $this->roleRepository->getById($id);
            if (!$role) {
                return ResponseHelper::jsonResponse(false, 'Data Role Not Found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Data Role', new RoleResource($role), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        try {
            $role = $this->roleRepository->getById($id);
            if (!$role) {
                return ResponseHelper::jsonResponse(false, 'Data Role Not Found', null, 404);
            }

            $role = $this->roleRepository->update($validated, $id);

            return ResponseHelper::jsonResponse(true, 'Data Role Updated', new RoleResource($role), 200);
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
            $role = $this->roleRepository->getById($id);
            if (!$role) {
                return ResponseHelper::jsonResponse(false, 'Data Role Not Found', null, 404);
            }

            $this->roleRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Data Role Deleted', null, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
