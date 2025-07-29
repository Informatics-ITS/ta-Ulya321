<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $userId = $request->input('user_id');
            $orders = Order::with(['orderItems.menu.vendor', 'user'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            $baseUrl = url('/');
            $ordersData = $orders->map(function ($order) use ($baseUrl) {
                return [
                    'order_id' => $order->order_id,
                    'room_number' => $order->room_number,
                    'building_name' => $order->building_name,
                    'courier_name' => $order->courier_name,
                    'shipping_fee' => $order->shipping_fee,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'payment_proof' => $order->payment_proof,
                    'payment_proof_url' => $order->payment_proof ? $baseUrl . '/assets/payment_proof/' . $order->payment_proof : null,
                    'delivery_time' => $order->delivery_time,
                    'formatted_delivery_time' => $order->formatted_delivery_time,
                    'delivery_notes' => $order->delivery_notes,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'order_items' => $order->orderItems->map(function ($item) use ($baseUrl) {
                        return [
                            'order_item_id' => $item->order_item_id,
                            'menu_id' => $item->menu_id,
                            'quantity' => $item->quantity,
                            'price_each' => (float) $item->price_each,
                            'total_price' => (float) $item->total_price,
                            'notes' => $item->notes,
                            'menu' => [
                                'menu_id' => $item->menu->menu_id,
                                'name' => $item->menu->name,
                                'price' => (float) $item->menu->price,
                                'vendor' => [
                                    'vendor_id' => $item->menu->vendor->vendor_id,
                                    'name' => $item->menu->vendor->name
                                ]
                            ]
                        ];
                    })
                ];
            });
            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $ordersData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $userId = $request->input('user_id');
            $order = Order::with(['orderItems.menu.vendor', 'user'])
                ->where('user_id', $userId)
                ->where('order_id', $id)
                ->firstOrFail();
            $baseUrl = url('/');
            $orderData = [
                'order_id' => $order->order_id,
                'user' => [
                    'user_id' => $order->user->user_id,
                    'name' => $order->user->name,
                    'phone' => $order->user->phone ?? null,
                    'email' => $order->user->email ?? null
                ],
                'room_number' => $order->room_number,
                'building_name' => $order->building_name,
                'courier_name' => $order->courier_name,
                'shipping_fee' => (float) $order->shipping_fee,
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'payment_proof' => $order->payment_proof,
                'payment_proof_url' => $order->payment_proof ? $baseUrl . '/assets/payment_proof/' . $order->payment_proof : null,
                'has_payment_proof' => !empty($order->payment_proof),
                'delivery_time' => $order->delivery_time ? $order->delivery_time->toISOString() : null,
                'formatted_delivery_time' => $order->formatted_delivery_time,
                'delivery_date' => $order->delivery_date,
                'delivery_time_only' => $order->delivery_time_only,
                'delivery_notes' => $order->delivery_notes,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'order_items' => $order->orderItems->map(function ($item) use ($baseUrl) {
                    return [
                        'order_item_id' => $item->order_item_id,
                        'menu_id' => $item->menu_id,
                        'quantity' => $item->quantity,
                        'price_each' => (float) $item->price_each,
                        'total_price' => (float) $item->total_price,
                        'notes' => $item->notes,
                        'menu' => [
                            'menu_id' => $item->menu->menu_id,
                            'name' => $item->menu->name,
                            'price' => (float) $item->menu->price,
                            'description' => $item->menu->description ?? null,
                            'vendor' => [
                                'vendor_id' => $item->menu->vendor->vendor_id,
                                'name' => $item->menu->vendor->name,
                                'description' => $item->menu->vendor->description,
                                'qris_image_url' => $item->menu->vendor->qris_image_url
                            ]
                        ]
                    ];
                })
            ];
            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $orderData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
                'room_number' => 'required|string|max:50',
                'building_name' => 'required|string|max:100',
                'courier_name' => 'nullable|string|max:100',
                'shipping_fee' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string|in:qris,cash,transfer,e-wallet',
                'delivery_time' => 'nullable|date|after:now',
                'delivery_notes' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.menu_id' => 'required|exists:menus,menu_id',
                'items.*.quantity' => 'required|integer|min:1|max:999',
                'items.*.notes' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            DB::beginTransaction();
            try {
                $userId = $request->input('user_id');
                $items = $request->input('items');
                $totalPrice = $request->input('shipping_fee');
                $vendorIds = [];
                foreach ($items as $item) {
                    $menu = Menu::with('vendor')->findOrFail($item['menu_id']);
                    if (!$menu->is_available) {
                        throw new Exception("Menu '{$menu->name}' is not available");
                    }
                    if (!$menu->vendor->is_active) {
                        throw new Exception("Vendor '{$menu->vendor->name}' is not active");
                    }
                    $totalPrice += $menu->price * $item['quantity'];
                    $vendorIds[] = $menu->vendor_id;
                }
                $uniqueVendorIds = array_unique($vendorIds);
                if (count($uniqueVendorIds) > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Orders can only contain items from one vendor'
                    ], 422);
                }
                $deliveryTime = null;
                if ($request->filled('delivery_time')) {
                    $deliveryTime = Carbon::parse($request->input('delivery_time'));
                    if ($deliveryTime->isPast()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Delivery time cannot be in the past',
                            'errors' => [
                                'delivery_time' => ['Delivery time must be in the future']
                            ]
                        ], 422);
                    }
                }
                $order = Order::create([
                    'user_id' => $userId,
                    'room_number' => $request->input('room_number'),
                    'building_name' => $request->input('building_name'),
                    'courier_name' => $request->input('courier_name'),
                    'shipping_fee' => $request->input('shipping_fee'),
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => $request->input('payment_method', 'qris'),
                    'delivery_time' => $deliveryTime,
                    'delivery_notes' => $request->input('delivery_notes')
                ]);
                foreach ($items as $item) {
                    $menu = Menu::findOrFail($item['menu_id']);
                    $order->orderItems()->create([
                        'menu_id' => $item['menu_id'],
                        'quantity' => $item['quantity'],
                        'price_each' => $menu->price,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
                DB::commit();
                $baseUrl = url('/');
                $order->load(['orderItems.menu.vendor', 'user']);
                $orderData = [
                    'order_id' => $order->order_id,
                    'user' => [
                        'user_id' => $order->user->user_id,
                        'name' => $order->user->name
                    ],
                    'room_number' => $order->room_number,
                    'building_name' => $order->building_name,
                    'courier_name' => $order->courier_name,
                    'shipping_fee' => (float) $order->shipping_fee,
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'delivery_time' => $order->delivery_time ? $order->delivery_time->toISOString() : null,
                    'formatted_delivery_time' => $order->formatted_delivery_time,
                    'delivery_notes' => $order->delivery_notes,
                    'created_at' => $order->created_at->toISOString(),
                    'updated_at' => $order->updated_at->toISOString(),
                    'order_items' => $order->orderItems->map(function ($item) use ($baseUrl) {
                        return [
                            'order_item_id' => $item->order_item_id,
                            'menu_id' => $item->menu_id,
                            'quantity' => $item->quantity,
                            'price_each' => (float) $item->price_each,
                            'total_price' => (float) $item->total_price,
                            'notes' => $item->notes,
                            'menu' => [
                                'menu_id' => $item->menu->menu_id,
                                'name' => $item->menu->name,
                                'price' => (float) $item->menu->price,
                                'vendor' => [
                                    'vendor_id' => $item->menu->vendor->vendor_id,
                                    'name' => $item->menu->vendor->name
                                ]
                            ]
                        ];
                    }),
                    'vendor' => [
                        'vendor_id' => $order->orderItems->first()->menu->vendor->vendor_id,
                        'name' => $order->orderItems->first()->menu->vendor->name,
                        'qris_image_url' => $order->orderItems->first()->menu->vendor->qris_image_url
                    ]
                ];
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $orderData
                ], 201);
            } catch (Exception $innerException) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $innerException->getMessage()
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function uploadPaymentProof(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
                'payment_proof_base64' => 'required|string',
                'file_extension' => 'required|string|in:jpg,jpeg,png,gif,pdf',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $baseUrl = url('/');
            $userId = $request->input('user_id');
            $order = Order::where('order_id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof already uploaded and verified'
                ], 400);
            }
            $base64Data = $request->input('payment_proof_base64');
            $fileExtension = $request->input('file_extension');
            if (preg_match('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+);base64,(.*)$/', $base64Data, $matches)) {
                $base64Data = $matches[2];
            }
            $decodedData = base64_decode($base64Data);
            if ($decodedData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid base64 data'
                ], 400);
            }
            if (strlen($decodedData) > 5 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size exceeds 5MB limit'
                ], 400);
            }
            $proofDir = public_path('assets/payment_proof');
            if (!is_dir($proofDir)) {
                mkdir($proofDir, 0755, true);
            }
            if ($order->payment_proof) {
                $oldProofPath = public_path('assets/payment_proof/' . $order->payment_proof);
                if (file_exists($oldProofPath)) {
                    unlink($oldProofPath);
                }
            }
            $proofName = 'proof_' . $order->order_id . '_' . time() . '_' . uniqid() . '.' . strtolower($fileExtension);
            $proofPath = $proofDir . '/' . $proofName;
            if (file_put_contents($proofPath, $decodedData) === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save payment proof file'
                ], 500);
            }
            $order->update([
                'payment_proof' => $proofName,
                'payment_status' => 'pending'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'payment_proof' => $order->payment_proof,
                    'payment_proof_url' => $baseUrl . '/assets/payment_proof/' . $order->payment_proof,
                    'payment_status' => $order->payment_status,
                    'file_size' => strlen($decodedData),
                    'uploaded_at' => now()->toISOString()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }
    public function updateDeliveryTime(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
                'delivery_time' => 'nullable|date|after:now',
                'delivery_notes' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $userId = $request->input('user_id');
            $order = Order::where('order_id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            if ($order->status === 'completed' || $order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update delivery time for completed or cancelled orders'
                ], 400);
            }
            $updateData = [];
            if ($request->filled('delivery_time')) {
                $deliveryTime = Carbon::parse($request->input('delivery_time'));
                if ($deliveryTime->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Delivery time cannot be in the past',
                        'errors' => [
                            'delivery_time' => ['Delivery time must be in the future']
                        ]
                    ], 422);
                }
                $updateData['delivery_time'] = $deliveryTime;
            } elseif ($request->has('delivery_time') && is_null($request->input('delivery_time'))) {
                $updateData['delivery_time'] = null;
            }
            if ($request->has('delivery_notes')) {
                $updateData['delivery_notes'] = $request->input('delivery_notes');
            }
            if (!empty($updateData)) {
                $order->update($updateData);
            }
            return response()->json([
                'success' => true,
                'message' => 'Delivery information updated successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'delivery_time' => $order->delivery_time,
                    'formatted_delivery_time' => $order->formatted_delivery_time,
                    'delivery_notes' => $order->delivery_notes
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }
    public function cancelOrder(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
                'reason' => 'nullable|string|max:255'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $userId = $request->input('user_id');
            $order = Order::where('order_id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            if ($order->payment_status === 'paid' || $order->status === 'processing' || $order->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel an order that has been paid or is already being processed'
                ], 400);
            }
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already cancelled'
                ], 400);
            }
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'updated_at' => $order->updated_at->toISOString()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }

    public function getVendorQris(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,user_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->input('user_id');

            $order = Order::with(['orderItems.menu.vendor'])
                ->where('user_id', $userId)
                ->where('order_id', $id)
                ->firstOrFail();

            $vendor = $order->orderItems->first()->menu->vendor;

            if (!$vendor->qris_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRIS not available for this vendor'
                ], 404);
            }

            if (!$vendor->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor is not active'
                ], 400);
            }

            $vendorQrisData = [
                'order_id' => $order->order_id,
                'vendor' => [
                    'vendor_id' => $vendor->vendor_id,
                    'name' => $vendor->name,
                    'description' => $vendor->description,
                    'qris_image' => $vendor->qris_image,
                    'qris_image_url' => $vendor->qris_image_url,
                    'is_active' => $vendor->is_active
                ],
                'order_info' => [
                    'total_price' => (float) $order->total_price,
                    'shipping_fee' => (float) $order->shipping_fee,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Vendor QRIS retrieved successfully',
                'data' => $vendorQrisData
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied'
            ], 404);
        }
    }
}