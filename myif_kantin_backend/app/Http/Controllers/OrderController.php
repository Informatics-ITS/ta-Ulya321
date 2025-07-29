<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::with(['user', 'orderItems.menu.vendor'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $vendors = Vendor::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $menus = Menu::where('is_available', true)
                ->with('vendor')
                ->orderBy('name')
                ->get();
            
            $users = User::orderBy('name')->get();
            
            return view('content.orders.index', compact('orders', 'vendors', 'menus', 'users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to load orders: ' . $e->getMessage());
        }
    }

    public function saveOrder(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'room_number' => 'required|string|max:50',
            'building_name' => 'required|string|max:100',
            'shipping_fee' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'required|string|in:pending,processing,on_delivery,completed,cancelled',
            'payment_status' => 'required|string|in:unpaid,pending,approved,rejected',
            'courier_name' => 'nullable|string|max:100',
            'delivery_time' => 'nullable|date|after:now',
            'delivery_notes' => 'nullable|string|max:500',
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            'menu_items' => 'sometimes|array|min:1',
            'menu_items.*.menu_id' => 'sometimes|required|exists:menus,menu_id',
            'menu_items.*.quantity' => 'sometimes|required|integer|min:1|max:999',
            'menu_items.*.notes' => 'sometimes|nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = $validatedData['shipping_fee'];
            
            if (isset($validatedData['menu_items']) && is_array($validatedData['menu_items'])) {
                foreach ($validatedData['menu_items'] as $item) {
                    $menu = Menu::findOrFail($item['menu_id']);
                    if (!$menu->is_available) {
                        throw new Exception("Menu '{$menu->name}' is not available");
                    }
                    $totalPrice += $menu->price * $item['quantity'];
                }
            }

            $deliveryTime = null;
            if (!empty($validatedData['delivery_time'])) {
                $deliveryTime = Carbon::parse($validatedData['delivery_time']);
                
                if ($deliveryTime->isPast()) {
                    throw new Exception("Delivery time cannot be in the past");
                }
            }

            $orderData = [
                'user_id' => $validatedData['user_id'],
                'room_number' => $validatedData['room_number'],
                'building_name' => $validatedData['building_name'],
                'courier_name' => $validatedData['courier_name'] ?? null,
                'shipping_fee' => $validatedData['shipping_fee'],
                'total_price' => $totalPrice,
                'status' => $validatedData['status'],
                'payment_status' => $validatedData['payment_status'],
                'payment_method' => $validatedData['payment_method'] ?? null,
                'delivery_time' => $deliveryTime,
                'delivery_notes' => $validatedData['delivery_notes'] ?? null,
            ];

            if ($request->hasFile('payment_proof')) {
                $proofDir = public_path('assets/payment_proof');
                if (!is_dir($proofDir)) {
                    mkdir($proofDir, 0755, true);
                }

                $proofFile = $request->file('payment_proof');
                $proofName = time() . '_' . uniqid() . '.' . $proofFile->getClientOriginalExtension();
                $proofFile->move($proofDir, $proofName);
                $orderData['payment_proof'] = $proofName;
                
                if ($validatedData['payment_status'] == 'unpaid') {
                    $orderData['payment_status'] = 'pending';
                }
            }

            if ($request->order_id) {
                $order = Order::findOrFail($request->order_id);
                
                if ($request->hasFile('payment_proof') && $order->payment_proof) {
                    $oldProofPath = public_path('assets/payment_proof/' . $order->payment_proof);
                    if (file_exists($oldProofPath)) {
                        unlink($oldProofPath);
                    }
                }
                
                $order->update($orderData);
                
                if (isset($validatedData['menu_items']) && is_array($validatedData['menu_items'])) {
                    OrderItem::where('order_id', $order->order_id)->delete();
                    
                    foreach ($validatedData['menu_items'] as $item) {
                        $menu = Menu::findOrFail($item['menu_id']);
                        OrderItem::create([
                            'order_id' => $order->order_id,
                            'menu_id' => $item['menu_id'],
                            'quantity' => $item['quantity'],
                            'price_each' => $menu->price,
                            'notes' => $item['notes'] ?? null,
                        ]);
                    }
                }
                
                $message = 'Order updated successfully';
            } else {
                $order = Order::create($orderData);
                
                if (isset($validatedData['menu_items']) && is_array($validatedData['menu_items'])) {
                    foreach ($validatedData['menu_items'] as $item) {
                        $menu = Menu::findOrFail($item['menu_id']);
                        OrderItem::create([
                            'order_id' => $order->order_id,
                            'menu_id' => $item['menu_id'],
                            'quantity' => $item['quantity'],
                            'price_each' => $menu->price,
                            'notes' => $item['notes'] ?? null,
                        ]);
                    }
                }
                
                $message = 'Order created successfully';
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', $message);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save order: ' . $e->getMessage());
        }
    }

    public function deleteOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            
            if ($order->payment_proof) {
                $proofPath = public_path('assets/payment_proof/' . $order->payment_proof);
                if (file_exists($proofPath)) {
                    unlink($proofPath);
                }
            }
            
            OrderItem::where('order_id', $order->order_id)->delete();
            $order->delete();
            
            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('orders.index')->with('error', 'Failed to delete order: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'status' => 'required|in:pending,processing,on_delivery,completed,cancelled',
            'courier_name' => 'nullable|string|max:100',
            'delivery_time' => 'nullable|date|after:now',
        ]);

        try {
            $order = Order::findOrFail($validatedData['order_id']);
            
            $updateData = [
                'status' => $validatedData['status'],
                'courier_name' => $validatedData['courier_name'] ?? $order->courier_name
            ];

            if (!empty($validatedData['delivery_time'])) {
                $deliveryTime = Carbon::parse($validatedData['delivery_time']);
                if ($deliveryTime->isPast()) {
                    throw new Exception("Delivery time cannot be in the past");
                }
                $updateData['delivery_time'] = $deliveryTime;
            }

            $order->update($updateData);

            return redirect()->route('orders.index')->with('success', 'Order status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    public function updatePaymentStatus(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'payment_status' => 'required|in:approved,rejected',
        ]);

        try {
            $order = Order::findOrFail($validatedData['order_id']);
            
            if (!$order->payment_proof) {
                throw new Exception("Cannot update payment status without payment proof");
            }
            
            $order->update([
                'payment_status' => $validatedData['payment_status']
            ]);

            return redirect()->route('orders.index')->with('success', 'Payment status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('orders.index')->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }
    
    public function getMenusByVendor(Request $request)
    {
        try {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,vendor_id'
            ]);

            $menus = Menu::where('vendor_id', $request->vendor_id)
                        ->where('is_available', true)
                        ->orderBy('name')
                        ->get(['menu_id', 'name', 'price']);
                        
            return response()->json($menus);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to load menus'], 500);
        }
    }
    
    public function getOrderDetails($id)
    {
        try {
            $order = Order::with(['user', 'orderItems.menu.vendor'])->findOrFail($id);
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    public function pollNewOrders(Request $request)
    {
        try {
            $lastId = $request->input('last_id', 0);
            
            $newOrders = Order::with(['user', 'orderItems.menu.vendor'])
                ->where('order_id', '>', $lastId)
                ->orderBy('order_id', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'orders' => $newOrders,
                'last_id' => $newOrders->isNotEmpty() ? $newOrders->first()->order_id : $lastId
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch new orders'], 500);
        }
    }

    public function pollOrderChanges(Request $request)
    {
        try {
            $lastUpdateTime = $request->input('last_update', now()->subMinutes(1)->toDateTimeString());
            
            $changedOrders = Order::with(['user', 'orderItems.menu.vendor'])
                ->where('updated_at', '>', $lastUpdateTime)
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'orders' => $changedOrders,
                'current_time' => now()->toDateTimeString()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'orders' => [],
                'current_time' => now()->toDateTimeString(),
                'error' => 'Failed to fetch order changes'
            ], 500);
        }
    }

    public function getVendorQris($vendorId)
    {
        try {
            $vendor = Vendor::findOrFail($vendorId);
            
            if (!$vendor->qris_image) {
                return response()->json(['error' => 'No QRIS image available for this vendor'], 404);
            }
            
            return response()->json([
                'vendor_name' => $vendor->name,
                'qris_url' => $vendor->qris_image_url
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }
    }

    public function exportReport(Request $request)
    {
        $validatedData = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,vendor_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
            $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();

            $query = Order::with(['user', 'orderItems.menu.vendor'])
                ->whereDate('created_at', '>=', $startDate->toDateString())
                ->whereDate('created_at', '<=', $endDate->toDateString());

            if (!empty($validatedData['vendor_id'])) {
                $query->whereHas('orderItems.menu', function($q) use ($validatedData) {
                    $q->where('vendor_id', $validatedData['vendor_id']);
                });
            }

            $allOrders = $query->orderBy('created_at', 'asc')->get();

            if ($allOrders->isEmpty()) {
                return redirect()->back()->with('error', 'No orders found for the selected date range and vendor.');
            }

            $orders = $allOrders->filter(function($order) {
                return $order->status === 'completed' && $order->payment_status === 'approved';
            });

            $totalRevenue = 0;
            $totalOrderAmount = 0;
            $totalCourierFee = 0;
            $vendorBreakdown = [];

            foreach ($orders as $order) {
                $totalRevenue += $order->total_price;
                $totalCourierFee += $order->shipping_fee;
                $orderSubtotal = $order->total_price - $order->shipping_fee;
                $totalOrderAmount += $orderSubtotal;

                foreach ($order->orderItems as $item) {
                    $vendorName = $item->menu->vendor->name;
                    if (!isset($vendorBreakdown[$vendorName])) {
                        $vendorBreakdown[$vendorName] = [
                            'total_orders' => 0,
                            'total_amount' => 0,
                            'orders_count' => 0
                        ];
                    }
                    $vendorBreakdown[$vendorName]['total_amount'] += ($item->price_each * $item->quantity);
                }
            }

            foreach ($orders as $order) {
                $vendorNames = [];
                foreach ($order->orderItems as $item) {
                    $vendorName = $item->menu->vendor->name;
                    if (!in_array($vendorName, $vendorNames)) {
                        $vendorNames[] = $vendorName;
                        $vendorBreakdown[$vendorName]['orders_count']++;
                    }
                }
            }

            $vendorName = 'All Vendors';
            if (!empty($validatedData['vendor_id'])) {
                $vendor = Vendor::find($validatedData['vendor_id']);
                $vendorName = $vendor ? $vendor->name : 'Unknown Vendor';
            }

            $reportData = [
                'vendor_name' => $vendorName,
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'orders' => $orders,
                'all_orders' => $allOrders,
                'total_revenue' => $totalRevenue,
                'total_order_amount' => $totalOrderAmount,
                'total_courier_fee' => $totalCourierFee,
                'vendor_breakdown' => $vendorBreakdown,
                'orders_count' => $orders->count(),
                'all_orders_count' => $allOrders->count(),
                'generated_at' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = \PDF::loadView('reports.orders', $reportData);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'orders_report_' . $validatedData['start_date'] . '_to_' . $validatedData['end_date'] . '.pdf';
            
            return $pdf->download($filename);

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }
}