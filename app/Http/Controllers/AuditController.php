<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Http\Resources\AuditResource;
use App\Http\Resources\PaginatedResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    private function getAuditsQuery(string $type, Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'event' => 'nullable|string|in:created,updated,deleted,restored',
            'auditable_id' => 'nullable|string',
            'row_per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Audit::query()
            ->with('user')
            ->where('auditable_type', $type);

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_id')) {
            $query->where('auditable_id', $request->auditable_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('new_values', 'like', "%{$search}%")
                  ->orWhere('old_values', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->row_per_page ?? 10;
        return $query->paginate($perPage);
    }

    public function userAudits(Request $request)
    {
        try {
            $audits = $this->getAuditsQuery(\App\Models\User::class, $request);
            return ResponseHelper::jsonResponse(true, 'User Audits Data', PaginatedResource::make($audits, AuditResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function roleAudits(Request $request)
    {
        try {
            $audits = $this->getAuditsQuery(\App\Models\Role::class, $request);
            return ResponseHelper::jsonResponse(true, 'Role Audits Data', PaginatedResource::make($audits, AuditResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function categoryAudits(Request $request)
    {
        try {
            $audits = $this->getAuditsQuery(\App\Models\Category::class, $request);
            return ResponseHelper::jsonResponse(true, 'Category Audits Data', PaginatedResource::make($audits, AuditResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function productAudits(Request $request)
    {
        try {
            $audits = $this->getAuditsQuery(\App\Models\Product::class, $request);
            return ResponseHelper::jsonResponse(true, 'Product Audits Data', PaginatedResource::make($audits, AuditResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
