<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Exception;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $vendors = Vendor::where('is_active', true)
                ->select('vendor_id', 'name', 'description', 'qris_image', 'is_active')
                ->get()
                ->map(function ($vendor) {
                    return [
                        'vendor_id' => $vendor->vendor_id,
                        'name' => $vendor->name,
                        'description' => $vendor->description,
                        'qris_image' => $vendor->qris_image,
                        'qris_image_url' => $vendor->qris_image_url,
                        'is_active' => $vendor->is_active,
                        'has_qris' => !empty($vendor->qris_image)
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Vendors retrieved successfully',
                'data' => $vendors
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
            $vendor = Vendor::where('vendor_id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            $vendorData = [
                'vendor_id' => $vendor->vendor_id,
                'name' => $vendor->name,
                'description' => $vendor->description,
                'qris_image' => $vendor->qris_image,
                'qris_image_url' => $vendor->qris_image_url,
                'is_active' => $vendor->is_active,
                'has_qris' => !empty($vendor->qris_image)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Vendor retrieved successfully',
                'data' => $vendorData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found or inactive'
            ], 404);
        }
    }

    public function getQris($id): JsonResponse
    {
        try {
            $vendor = Vendor::where('vendor_id', $id)
                ->where('is_active', true)
                ->firstOrFail();

            if (!$vendor->qris_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRIS not available for this vendor'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'QRIS retrieved successfully',
                'data' => [
                    'vendor_id' => $vendor->vendor_id,
                    'vendor_name' => $vendor->name,
                    'qris_image' => $vendor->qris_image,
                    'qris_image_url' => $vendor->qris_image_url
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found or QRIS not available'
            ], 404);
        }
    }
}