<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\UserResource;
use App\Interface\UserInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserInterface $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
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
            $users = $this->userRepository->getAll(
                $request->search,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Data User', UserResource::collection($users), 200);
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
            $users = $this->userRepository->getAllPaginated(
                $request->search,
                $request->row_per_page
            );

            return ResponseHelper::jsonResponse(true, 'Data User', PaginatedResource::make($users, UserResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $request = $request->validated();
        try {
            $request = $this->userRepository->create($request);

            return ResponseHelper::jsonResponse(true, 'Data User', new UserResource($request), 201);
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
            $user = $this->userRepository->getById($id);
            if (!$user) {
                return ResponseHelper::jsonResponse(false, 'Data User Not Found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Data User', new UserResource($user), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $request = $request->validated();
        try {
            $user = $this->userRepository->getById($id);
            if (!$user) {
                return ResponseHelper::jsonResponse(false, 'Data User Not Found', null, 404);
            }

            $user = $this->userRepository->update($request, $id);

            return ResponseHelper::jsonResponse(true, 'Data User', new UserResource($user), 200);
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
            $user = $this->userRepository->getById($id);
            if (!$user) {
                return ResponseHelper::jsonResponse(false, 'Data User Not Found', null, 404);
            }

            $user = $this->userRepository->delete($id);

            return ResponseHelper::jsonResponse(true, 'Data User Deleted', null, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
