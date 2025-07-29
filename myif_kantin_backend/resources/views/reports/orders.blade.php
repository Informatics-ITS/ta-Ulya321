<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-label {
            font-weight: bold;
            width: 60%;
        }
        .summary-value {
            text-align: right;
            width: 40%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .vendor-breakdown {
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .debug-info {
            background: #fff3cd;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orders Report</h1>
        <p><strong>Vendor:</strong> {{ $vendor_name }}</p>
        <p><strong>Period:</strong> {{ date('d/m/Y', strtotime($start_date)) }} - {{ date('d/m/Y', strtotime($end_date)) }}</p>
        <p><strong>Generated:</strong> {{ $generated_at }}</p>
    </div>

    @if(isset($all_orders_count))
    <div class="debug-info">
        <h4>Data Analysis</h4>
        <p><strong>Total Orders in Date Range:</strong> {{ $all_orders_count }}</p>
        <p><strong>Completed & Approved Orders:</strong> {{ $orders_count }}</p>
        @if($all_orders_count > 0 && $orders_count == 0)
            <p style="color: red;"><strong>Note:</strong> No orders have both 'completed' status AND 'approved' payment status in this period.</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">Completed & Approved Orders:</div>
                <div class="summary-cell summary-value">{{ $orders_count }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">Total Order Amount:</div>
                <div class="summary-cell summary-value">Rp {{ number_format($total_order_amount, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">Total Courier Fee:</div>
                <div class="summary-cell summary-value">Rp {{ number_format($total_courier_fee, 0, ',', '.') }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">Total Revenue:</div>
                <div class="summary-cell summary-value"><strong>Rp {{ number_format($total_revenue, 0, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

    @if(count($vendor_breakdown) > 1)
    <div class="vendor-breakdown">
        <h3>Vendor Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th class="text-center">Orders Count</th>
                    <th class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendor_breakdown as $vendorName => $data)
                <tr>
                    <td>{{ $vendorName }}</td>
                    <td class="text-center">{{ $data['orders_count'] }}</td>
                    <td class="text-right">Rp {{ number_format($data['total_amount'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div>
        <h3>Order Details - Completed & Approved Orders</h3>
        @if(count($orders) > 0)
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Vendor</th>
                    <th class="text-right">Order Amount</th>
                    <th class="text-right">Courier Fee</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>#{{ $order->order_id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>
                        @php
                            $orderVendors = $order->orderItems->map(function($item) {
                                return $item->menu->vendor->name;
                            })->unique()->implode(', ');
                        @endphp
                        {{ $orderVendors }}
                    </td>
                    <td class="text-right">Rp {{ number_format($order->total_price - $order->shipping_fee, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No completed and approved orders found in this date range.</p>
        @endif
    </div>

    @if(isset($all_orders) && count($all_orders) > 0 && count($orders) == 0)
    <div>
        <h3>All Orders in Date Range (For Reference)</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($all_orders as $order)
                <tr>
                    <td>#{{ $order->order_id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                    <td class="text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically on {{ $generated_at }}</p>
    </div>
</body>
</html>