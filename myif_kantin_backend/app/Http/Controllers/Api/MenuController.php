<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Menu::with('vendor')
                ->where('is_available', true)
                ->whereHas('vendor', function ($q) {
                    $q->where('is_active', true);
                });

            if ($request->has('vendor_id')) {
                $query->where('vendor_id', $request->input('vendor_id'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $baseUrl = url('/');

            $menus = $query->orderBy('name')->get()->map(function ($menu) use ($baseUrl) {
                return [
                    'menu_id' => $menu->menu_id,
                    'name' => $menu->name,
                    'description' => $menu->description,
                    'price' => (float) $menu->price,
                    'photo_url' => $menu->photo_url ? $baseUrl . '/' . ltrim($menu->photo_url, '/') : null,
                    'is_available' => $menu->is_available,
                    'vendor' => [
                        'vendor_id' => $menu->vendor->vendor_id,
                        'name' => $menu->vendor->name,
                        'description' => $menu->vendor->description,
                        'has_qris' => !empty($menu->vendor->qris_image)
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Menus retrieved successfully',
                'data' => $menus
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $menu = Menu::with('vendor')
                ->where('menu_id', $id)
                ->where('is_available', true)
                ->whereHas('vendor', function ($q) {
                    $q->where('is_active', true);
                })
                ->firstOrFail();

            $baseUrl = url('/');

            $menuData = [
                'menu_id' => $menu->menu_id,
                'name' => $menu->name,
                'description' => $menu->description,
                'price' => (float) $menu->price,
                'photo_url' => $menu->photo_url ? $baseUrl . '/' . ltrim($menu->photo_url, '/') : null,
                'is_available' => $menu->is_available,
                'vendor' => [
                    'vendor_id' => $menu->vendor->vendor_id,
                    'name' => $menu->vendor->name,
                    'description' => $menu->vendor->description,
                    'qris_image_url' => $menu->vendor->qris_image ? $baseUrl . '/' . ltrim($menu->vendor->qris_image, '/') : null,
                    'has_qris' => !empty($menu->vendor->qris_image),
                    'is_active' => $menu->vendor->is_active
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Menu retrieved successfully',
                'data' => $menuData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found or unavailable'
            ], 404);
        }
    }

    public function getByVendor($vendorId): JsonResponse
    {
        try {
            $validator = Validator::make(['vendor_id' => $vendorId], [
                'vendor_id' => 'required|exists:vendors,vendor_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vendor = Vendor::where('vendor_id', $vendorId)
                ->where('is_active', true)
                ->firstOrFail();

            $baseUrl = url('/');

            $menus = Menu::where('vendor_id', $vendorId)
                ->where('is_available', true)
                ->orderBy('name')
                ->get()
                ->map(function ($menu) use ($baseUrl) {
                    return [
                        'menu_id' => $menu->menu_id,
                        'name' => $menu->name,
                        'description' => $menu->description,
                        'price' => (float) $menu->price,
                        'photo_url' => $menu->photo_url ? $baseUrl . '/' . ltrim($menu->photo_url, '/') : null,
                        'is_available' => $menu->is_available
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Vendor menus retrieved successfully',
                'data' => [
                    'vendor' => [
                        'vendor_id' => $vendor->vendor_id,
                        'name' => $vendor->name,
                        'description' => $vendor->description,
                        'qris_image_url' => $vendor->qris_image ? $baseUrl . '/' . ltrim($vendor->qris_image, '/') : null,
                        'has_qris' => !empty($vendor->qris_image)
                    ],
                    'menus' => $menus
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found or inactive'
            ], 404);
        }
    }
}
