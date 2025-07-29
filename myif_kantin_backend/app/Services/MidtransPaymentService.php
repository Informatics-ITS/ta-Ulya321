<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class MidtransPaymentService
{
    protected $serverKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->isProduction = config('midtrans.is_production', false);
        $this->baseUrl = $this->isProduction 
            ? 'https://api.midtrans.com' 
            : 'https://api.sandbox.midtrans.com';
    }

    public function createQrisTransaction(Order $order)
    {
        try {
            $midtransOrderId = 'ORD-' . time() . '-' . Str::upper(Str::random(6));
            $order->update(['midtrans_order_id' => $midtransOrderId]);

            $items = [];
            foreach ($order->orderItems as $item) {
                $items[] = [
                    'id' => $item->menu_id,
                    'price' => (float) $item->price_each,
                    'quantity' => $item->quantity,
                    'name' => $item->menu->name ?? "Menu #{$item->menu_id}"
                ];
            }

            if ($order->shipping_fee > 0) {
                $items[] = [
                    'id' => 'SHIPPING',
                    'price' => (float) $order->shipping_fee,
                    'quantity' => 1,
                    'name' => 'Ongkos Kirim'
                ];
            }

            $customerDetails = [
                'first_name' => $order->user->name ?? 'Customer',
                'email' => $order->user->email ?? 'customer@example.com',
                'phone' => $order->user->phone ?? ''
            ];

            $payload = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId, // Gunakan ID baru
                    'gross_amount' => (float) $order->total_price
                ],
                'item_details' => $items,
                'customer_details' => $customerDetails,
                'payment_type' => 'qris',
                'qris' => [
                    'acquirer' => 'gopay'
                ]
            ];

            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/v2/charge', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                $order->update([
                    'payment_method' => 'qris',
                    'payment_status' => 'pending'
                ]);

                return [
                    'success' => true,
                    'message' => 'QRIS payment created successfully',
                    'data' => [
                        'qr_code_url' => $responseData['actions'][0]['url'] ?? null,
                        'qr_string' => $responseData['qr_string'] ?? null,
                        'transaction_id' => $responseData['transaction_id'] ?? null,
                        'order_id' => $midtransOrderId,
                        'expiry_time' => $responseData['expiry_time'] ?? null,
                    ]
                ];
            }

            throw new Exception('Failed to create QRIS payment: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function checkTransactionStatus(string $orderId)
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get($this->baseUrl . '/v2/' . $orderId . '/status');

            if ($response->successful()) {
                $responseData = $response->json();
                
                $order = Order::where('midtrans_order_id', $orderId)->first();
                
                if ($order) {
                    $transactionStatus = $responseData['transaction_status'] ?? null;
                    $fraudStatus = $responseData['fraud_status'] ?? null;
                    
                    $paymentStatus = 'pending';
                    
                    if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                        if ($fraudStatus == 'accept') {
                            $paymentStatus = 'paid';
                            $orderStatus = 'processing';
                        }
                    } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                        $paymentStatus = 'failed';
                        $orderStatus = 'cancelled';
                    } elseif ($transactionStatus == 'pending') {
                        $paymentStatus = 'pending';
                        $orderStatus = 'pending';
                    }
                    
                    $order->update([
                        'payment_status' => $paymentStatus,
                        'status' => $orderStatus ?? $order->status
                    ]);
                }
                
                return [
                    'success' => true,
                    'message' => 'Transaction status retrieved successfully',
                    'data' => $responseData
                ];
            }
            
            throw new Exception('Failed to check transaction status: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function handleNotification(array $notificationData)
    {
        try {
            $orderId = $notificationData['order_id'] ?? null;
            $transactionStatus = $notificationData['transaction_status'] ?? null;
            $fraudStatus = $notificationData['fraud_status'] ?? null;
            
            if (empty($orderId) || empty($transactionStatus)) {
                throw new Exception('Invalid notification data');
            }
            
            $verificationResponse = $this->checkTransactionStatus($orderId);
            
            if (!$verificationResponse['success']) {
                throw new Exception('Failed to verify transaction status');
            }
            
            return [
                'success' => true,
                'message' => 'Notification handled successfully',
                'data' => $notificationData
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    public function cancelTransaction(Order $order)
    {
        try {
            if (empty($order->midtrans_order_id)) {
                throw new Exception('Order does not have a Midtrans transaction ID');
            }
            
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/v2/' . $order->midtrans_order_id . '/cancel');
                
            if ($response->successful()) {
                $responseData = $response->json();
                
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Transaction cancelled successfully',
                    'data' => $responseData
                ];
            }
            
            throw new Exception('Failed to cancel transaction: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    public function refundTransaction(Order $order, float $amount = null, string $reason = 'Customer request')
    {
        try {
            if (empty($order->midtrans_order_id)) {
                throw new Exception('Order does not have a Midtrans transaction ID');
            }
            
            $amount = $amount ?? (float) $order->total_price;
            
            $payload = [
                'refund_key' => 'REF-' . time() . '-' . Str::upper(Str::random(6)),
                'amount' => $amount,
                'reason' => $reason
            ];
            
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/v2/' . $order->midtrans_order_id . '/refund', $payload);
                
            if ($response->successful()) {
                $responseData = $response->json();
                
                $order->update([
                    'payment_status' => 'refunded',
                    'status' => 'refunded'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Transaction refunded successfully',
                    'data' => $responseData
                ];
            }
            
            throw new Exception('Failed to refund transaction: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    public function expireTransaction(Order $order)
    {
        try {
            if (empty($order->midtrans_order_id)) {
                throw new Exception('Order does not have a Midtrans transaction ID');
            }
            
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/v2/' . $order->midtrans_order_id . '/expire');
                
            if ($response->successful()) {
                $responseData = $response->json();
                
                $order->update([
                    'payment_status' => 'expired',
                    'status' => 'cancelled'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Transaction expired successfully',
                    'data' => $responseData
                ];
            }
            
            throw new Exception('Failed to expire transaction: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    public function getTransactionDetails(string $orderId)
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get($this->baseUrl . '/v2/' . $orderId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Transaction details retrieved successfully',
                    'data' => $response->json()
                ];
            }
            
            throw new Exception('Failed to get transaction details: ' . $response->body());
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}