@extends('layouts.app')

@section('title', 'Orders Management')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Data Orders</h6>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                            <strong>Error!</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Filter by Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="on_delivery">On Delivery</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="ordersTable" class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Vendors</th>
                                    <th>Address</th>
                                    <th>Delivery Time</th>
                                    <th>Courier</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="orders-tbody">
                                @foreach ($orders as $order)
                                    <tr data-order="{{ json_encode($order) }}" id="order-row-{{ $order->order_id }}">
                                        <td>#{{ $order->order_id }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>
                                            @php
                                                $orderVendors = $order->orderItems->map(function($item) {
                                                    return $item->menu->vendor->name;
                                                })->unique()->implode(', ');
                                            @endphp
                                            <small class="text-muted">{{ $orderVendors }}</small>
                                        </td>
                                        <td>{{ $order->building_name }}, {{ $order->room_number }}</td>
                                        <td>
                                            @if($order->delivery_time)
                                                <small class="text-muted">{{ $order->formatted_delivery_time }}</small>
                                            @else
                                                <small class="text-muted">ASAP</small>
                                            @endif
                                        </td>
                                        <td>{{ $order->courier_name ?? 'Not assigned' }}</td>
                                        <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $order->status == 'completed' ? 'success' : 
                                                ($order->status == 'cancelled' ? 'danger' : 
                                                ($order->status == 'on_delivery' ? 'info' : 
                                                ($order->status == 'processing' ? 'warning' : 'secondary')))
                                            }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $order->payment_status == 'approved' ? 'success' : ($order->payment_status == 'rejected' ? 'danger' : ($order->payment_status == 'pending' ? 'warning' : 'secondary')) }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                            @if($order->payment_proof)
                                                <br><button type="button" class="btn btn-sm btn-outline-success mt-1 view-proof-btn" 
                                                    data-proof-url="{{ asset('assets/payment_proof/' . $order->payment_proof) }}" 
                                                    data-bs-toggle="modal" data-bs-target="#proofModal">
                                                    <i class="fas fa-file-image"></i> View Proof
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary view-btn"
                                                data-bs-toggle="modal" data-bs-target="#viewOrderModal" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($order->payment_proof && $order->payment_status == 'pending')
                                                <button type="button" class="btn btn-sm btn-info payment-status-btn"
                                                    data-bs-toggle="modal" data-bs-target="#paymentStatusModal" title="Update Payment Status">
                                                    <i class="fas fa-credit-card"></i>
                                                </button>
                                            @endif
                                            @if(in_array($order->payment_status, ['approved', 'rejected']) && !in_array($order->status, ['completed', 'cancelled']))
                                                <button type="button" class="btn btn-sm btn-success status-btn"
                                                    data-bs-toggle="modal" data-bs-target="#statusModal" title="Update Status">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete Order">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form method="POST" action="{{ route('orders.delete', $order->order_id) }}"
                                                style="display:none;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="proofModalLabel">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="proofImage" src="" alt="Payment Proof" class="img-fluid" style="max-height: 500px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadProof" href="" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewOrderModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%">Order ID</td>
                                    <td id="view-order-id"></td>
                                </tr>
                                <tr>
                                    <td>Customer</td>
                                    <td id="view-customer"></td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td id="view-address"></td>
                                </tr>
                                <tr>
                                    <td>Delivery Time</td>
                                    <td id="view-delivery-time"></td>
                                </tr>
                                <tr>
                                    <td>Courier</td>
                                    <td id="view-courier"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%">Status</td>
                                    <td id="view-status"></td>
                                </tr>
                                <tr>
                                    <td>Payment Status</td>
                                    <td id="view-payment-status"></td>
                                </tr>
                                <tr>
                                    <td>Payment Method</td>
                                    <td id="view-payment-method"></td>
                                </tr>
                                <tr>
                                    <td>Payment Proof</td>
                                    <td id="view-payment-proof"></td>
                                </tr>
                                <tr>
                                    <td>Order Date</td>
                                    <td id="view-order-date"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4" id="delivery-notes-section" style="display: none;">
                        <div class="col-md-12">
                            <h6>Delivery Notes</h6>
                            <div class="card">
                                <div class="card-body">
                                    <p id="view-delivery-notes" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Menu</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Notes</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="view-order-items">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Subtotal</strong></td>
                                    <td class="text-end" id="view-subtotal"></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Shipping Fee</strong></td>
                                    <td class="text-end" id="view-shipping-fee"></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total</strong></td>
                                    <td class="text-end" id="view-total-price"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentStatusModalLabel">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('orders.payment-status') }}" id="paymentStatusForm">
                    @csrf
                    <input type="hidden" name="order_id" id="payment_status_order_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="payment_status_update" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status_update" name="payment_status" required>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('orders.status') }}" id="statusForm">
                    @csrf
                    <input type="hidden" name="order_id" id="status_order_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status_update" class="form-label">Status</label>
                            <select class="form-select" id="status_update" name="status" required>
                            </select>
                        </div>
                        <div class="mb-3" id="courier_name_group" style="display: none;">
                            <label for="courier_name_update" class="form-label">Courier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="courier_name_update" name="courier_name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let lastOrderId = {{ $orders->count() > 0 ? $orders->first()->order_id : 0 }};
            let lastUpdateTime = '{{ now()->subMinutes(1)->toDateTimeString() }}';
            let pollingInterval = null;
            let isRequestInProgress = false;
            let ordersDataTable = null;

            function initializeDataTable() {
                if (ordersDataTable) {
                    ordersDataTable.destroy();
                }
                
                ordersDataTable = $('#ordersTable').DataTable({
                    "pageLength": 25,
                    "ordering": true,
                    "searching": true,
                    "paging": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "order": [[0, "desc"]],
                    "columnDefs": [
                        {
                            "targets": [9],
                            "orderable": false,
                            "searchable": false
                        },
                        {
                            "targets": [6],
                            "type": "num-fmt"
                        }
                    ],
                    "language": {
                        "search": "Search orders:",
                        "lengthMenu": "Show _MENU_ orders per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ orders",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    "dom": 'lrtip'
                });

                initializeFilters();
            }

            function initializeFilters() {
                $('#statusFilter').off('change.filters').on('change.filters', function() {
                    applyFilters();
                });

                $('#clearFilters').off('click.filters').on('click.filters', function() {
                    clearAllFilters();
                });
            }

            function applyFilters() {
                const statusFilter = $('#statusFilter').val();

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'ordersTable') {
                        return true;
                    }

                    const row = $(ordersDataTable.row(dataIndex).node());
                    const orderData = row.data('order');
                    
                    if (!orderData) {
                        return true;
                    }

                    if (statusFilter && orderData.status !== statusFilter) {
                        return false;
                    }

                    return true;
                });

                ordersDataTable.draw();
                $.fn.dataTable.ext.search.pop();
            }

            function clearAllFilters() {
                $('#statusFilter').val('');
                ordersDataTable.search('').draw();
            }

            function reinitializeDataTable() {
                setTimeout(() => {
                    const currentFilter = $('#statusFilter').val();
                    initializeDataTable();
                    
                    if (currentFilter) {
                        $('#statusFilter').val(currentFilter);
                        applyFilters();
                    }
                }, 100);
            }

            function initializeEventHandlers() {
                $(document).off('.orderEvents');

                $(document).on('click.orderEvents', '.view-btn', function() {
                    const row = $(this).closest('tr');
                    const order = row.data('order');

                    if (!order) return;

                    loadOrderDetails(order);
                });

                $(document).on('click.orderEvents', '.view-proof-btn', function() {
                    const proofUrl = $(this).data('proof-url');
                    $('#proofImage').attr('src', proofUrl);
                    $('#downloadProof').attr('href', proofUrl);
                });

                $(document).on('click.orderEvents', '.payment-status-btn', function() {
                    const row = $(this).closest('tr');
                    const order = row.data('order');

                    if (!order) return;

                    $('#payment_status_order_id').val(order.order_id);
                    $('#payment_status_update').val('approved');
                });

                $(document).on('click.orderEvents', '.status-btn', function() {
                    const row = $(this).closest('tr');
                    const order = row.data('order');

                    if (!order) return;

                    $('#status_order_id').val(order.order_id);
                    $('#courier_name_update').val(order.courier_name || '');
                    
                    populateStatusOptions(order.status);
                });

                $(document).on('change', '#status_update', function() {
                    const selectedStatus = $(this).val();
                    const courierGroup = $('#courier_name_group');
                    const courierInput = $('#courier_name_update');
                    
                    if (selectedStatus === 'on_delivery') {
                        courierGroup.show();
                        courierInput.attr('required', true);
                    } else {
                        courierGroup.hide();
                        courierInput.attr('required', false);
                        courierInput.val('');
                    }
                });

                $(document).on('click.orderEvents', '.delete-btn', function(e) {
                    e.preventDefault();
                    const form = $(this).siblings('.delete-form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the order and all its items. This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                $('#statusModal').on('hidden.bs.modal', function() {
                    $('#courier_name_group').hide();
                    $('#courier_name_update').attr('required', false);
                });
            }

            function populateStatusOptions(currentStatus) {
                const statusSelect = $('#status_update');
                const courierGroup = $('#courier_name_group');
                const courierInput = $('#courier_name_update');
                
                statusSelect.empty();
                courierGroup.hide();
                courierInput.attr('required', false);
                courierInput.val('');

                let allowedStatuses = [];

                switch(currentStatus) {
                    case 'pending':
                        allowedStatuses = [
                            { value: 'processing', text: 'Processing' },
                            { value: 'cancelled', text: 'Cancelled' }
                        ];
                        break;
                    case 'processing':
                        allowedStatuses = [
                            { value: 'on_delivery', text: 'On Delivery' },
                            { value: 'cancelled', text: 'Cancelled' }
                        ];
                        break;
                    case 'on_delivery':
                        allowedStatuses = [
                            { value: 'completed', text: 'Completed' }
                        ];
                        break;
                    default:
                        allowedStatuses = [];
                }

                allowedStatuses.forEach(function(status) {
                    statusSelect.append(`<option value="${status.value}">${status.text}</option>`);
                });

                if (allowedStatuses.length > 0) {
                    statusSelect.val(allowedStatuses[0].value);
                    
                    if (allowedStatuses[0].value === 'on_delivery') {
                        courierGroup.show();
                        courierInput.attr('required', true);
                    }
                }
            }

            function loadOrderDetails(order) {
                $('#view-order-id').text('#' + order.order_id);
                $('#view-customer').text(order.user.name);
                $('#view-address').text(order.building_name + ', ' + order.room_number);
                $('#view-courier').text(order.courier_name || 'Not assigned');
                
                if (order.delivery_time) {
                    const deliveryTime = new Date(order.delivery_time);
                    $('#view-delivery-time').text(deliveryTime.toLocaleString());
                } else {
                    $('#view-delivery-time').text('ASAP');
                }

                const statusBadgeClass = getStatusBadgeClass(order.status);
                const paymentStatusBadgeClass = getPaymentStatusBadgeClass(order.payment_status);

                $('#view-status').html(`<span class="badge ${statusBadgeClass}">${capitalizeFirst(order.status.replace('_', ' '))}</span>`);
                $('#view-payment-status').html(`<span class="badge ${paymentStatusBadgeClass}">${capitalizeFirst(order.payment_status)}</span>`);
                $('#view-payment-method').text(order.payment_method || 'Not specified');

                if (order.payment_proof) {
                    const proofUrl = `{{ asset('assets/payment_proof') }}/${order.payment_proof}`;
                    $('#view-payment-proof').html(`<button type="button" class="btn btn-sm btn-outline-primary view-proof-btn" data-proof-url="${proofUrl}" data-bs-toggle="modal" data-bs-target="#proofModal"><i class="fas fa-file"></i> View Proof</button>`);
                } else {
                    $('#view-payment-proof').html('<span class="text-muted">No proof uploaded</span>');
                }

                const orderDate = new Date(order.created_at);
                $('#view-order-date').text(orderDate.toLocaleString());

                if (order.delivery_notes) {
                    $('#view-delivery-notes').text(order.delivery_notes);
                    $('#delivery-notes-section').show();
                } else {
                    $('#delivery-notes-section').hide();
                }

                loadOrderItemsForView(order);
            }

            function loadOrderItemsForView(order) {
                let subtotal = 0;
                let itemsHtml = '';

                if (order.order_items && order.order_items.length > 0) {
                    order.order_items.forEach(function(item) {
                        const itemTotal = item.price_each * item.quantity;
                        subtotal += itemTotal;

                        let notesDisplay = '<span class="text-muted">-</span>';
                        if (item.notes) {
                            notesDisplay = `<small class="text-primary">${item.notes}</small>`;
                        }

                        itemsHtml += `
                            <tr>
                                <td>${item.menu.vendor.name}</td>
                                <td>${item.menu.name}</td>
                                <td>Rp ${formatNumber(item.price_each)}</td>
                                <td>${item.quantity}</td>
                                <td>${notesDisplay}</td>
                                <td class="text-end">Rp ${formatNumber(itemTotal)}</td>
                            </tr>
                        `;
                    });
                } else {
                    itemsHtml = '<tr><td colspan="6" class="text-center">No items in this order</td></tr>';
                }

                $('#view-order-items').html(itemsHtml);
                $('#view-subtotal').text('Rp ' + formatNumber(subtotal));
                $('#view-shipping-fee').text('Rp ' + formatNumber(order.shipping_fee));
                $('#view-total-price').text('Rp ' + formatNumber(order.total_price));
            }

            function startPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }

                pollingInterval = setInterval(function() {
                    if (isRequestInProgress) return;

                    isRequestInProgress = true;
                    
                    $.ajax({
                        url: "{{ route('orders.changes') }}",
                        type: "GET",
                        data: { last_update: lastUpdateTime },
                        timeout: 10000,
                        success: function(response) {
                            if (response.orders && response.orders.length > 0) {
                                lastUpdateTime = response.current_time;
                                
                                response.orders.forEach(function(order) {
                                    updateOrderRow(order);
                                });
                                
                                reinitializeDataTable();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Polling error:', error);
                        },
                        complete: function() {
                            isRequestInProgress = false;
                        }
                    });
                }, 5000);
            }

            function updateOrderRow(order) {
                const existingRow = $(`#order-row-${order.order_id}`);
                
                if (existingRow.length > 0) {
                    existingRow.attr('data-order', JSON.stringify(order));
                    
                    const statusBadgeClass = getStatusBadgeClass(order.status);
                    const paymentStatusBadgeClass = getPaymentStatusBadgeClass(order.payment_status);
                    
                    let deliveryTimeText = 'ASAP';
                    if (order.delivery_time) {
                        const deliveryTime = new Date(order.delivery_time);
                        deliveryTimeText = deliveryTime.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    let vendorsText = '';
                    if (order.order_items && order.order_items.length > 0) {
                        const vendors = [...new Set(order.order_items.map(item => item.menu.vendor.name))];
                        vendorsText = vendors.join(', ');
                    }
                    
                    let paymentStatusHtml = `<span class="badge ${paymentStatusBadgeClass}">${capitalizeFirst(order.payment_status)}</span>`;
                    if (order.payment_proof) {
                        const proofUrl = `{{ asset('assets/payment_proof') }}/${order.payment_proof}`;
                        paymentStatusHtml += `<br><button type="button" class="btn btn-sm btn-outline-success mt-1 view-proof-btn" data-proof-url="${proofUrl}" data-bs-toggle="modal" data-bs-target="#proofModal"><i class="fas fa-file-image"></i> View Proof</button>`;
                    }

                    let actionButtons = `
                        <button type="button" class="btn btn-sm btn-primary view-btn" data-bs-toggle="modal" data-bs-target="#viewOrderModal" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;

                    if (order.payment_proof && order.payment_status == 'pending') {
                        actionButtons += `
                            <button type="button" class="btn btn-sm btn-info payment-status-btn" data-bs-toggle="modal" data-bs-target="#paymentStatusModal" title="Update Payment Status">
                                <i class="fas fa-credit-card"></i>
                            </button>
                        `;
                    }

                    if (order.payment_status === 'approved' || order.payment_status === 'rejected') {
                        if (order.status !== 'completed' && order.status !== 'cancelled') {
                            actionButtons += `
                                <button type="button" class="btn btn-sm btn-success status-btn" data-bs-toggle="modal" data-bs-target="#statusModal" title="Update Status">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            `;
                        }
                    }

                    actionButtons += `
                        <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete Order">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form method="POST" action="{{ route('orders.delete', '') }}/${order.order_id}" style="display:none;" class="delete-form">
                            @csrf
                            @method('DELETE')
                        </form>
                    `;
                    
                    existingRow.find('td:eq(2)').html(`<small class="text-muted">${vendorsText}</small>`);
                    existingRow.find('td:eq(4)').html(`<small class="text-muted">${deliveryTimeText}</small>`);
                    existingRow.find('td:eq(5)').text(order.courier_name || 'Not assigned');
                    existingRow.find('td:eq(6)').text(`Rp ${formatNumber(order.total_price)}`);
                    existingRow.find('td:eq(7)').html(`<span class="badge ${statusBadgeClass}">${capitalizeFirst(order.status.replace('_', ' '))}</span>`);
                    existingRow.find('td:eq(8)').html(paymentStatusHtml);
                    existingRow.find('td:eq(9)').html(actionButtons);
                    
                    existingRow.addClass('status-update-highlight');
                    setTimeout(function() {
                        existingRow.removeClass('status-update-highlight');
                    }, 3000);
                } else {
                    addNewOrderRow(order);
                }
            }

            function addNewOrderRow(order) {
                if (order.order_id <= lastOrderId) return;
                
                lastOrderId = order.order_id;
                
                const statusBadgeClass = getStatusBadgeClass(order.status);
                const paymentStatusBadgeClass = getPaymentStatusBadgeClass(order.payment_status);
                
                let deliveryTimeText = 'ASAP';
                if (order.delivery_time) {
                    const deliveryTime = new Date(order.delivery_time);
                    deliveryTimeText = deliveryTime.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                let vendorsText = '';
                if (order.order_items && order.order_items.length > 0) {
                    const vendors = [...new Set(order.order_items.map(item => item.menu.vendor.name))];
                    vendorsText = vendors.join(', ');
                }
                
                let paymentStatusHtml = `<span class="badge ${paymentStatusBadgeClass}">${capitalizeFirst(order.payment_status)}</span>`;
                if (order.payment_proof) {
                    const proofUrl = `{{ asset('assets/payment_proof') }}/${order.payment_proof}`;
                    paymentStatusHtml += `<br><button type="button" class="btn btn-sm btn-outline-success mt-1 view-proof-btn" data-proof-url="${proofUrl}" data-bs-toggle="modal" data-bs-target="#proofModal"><i class="fas fa-file-image"></i> View Proof</button>`;
                }

                let actionButtons = `
                    <button type="button" class="btn btn-sm btn-primary view-btn" data-bs-toggle="modal" data-bs-target="#viewOrderModal" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                `;

                if (order.payment_proof && order.payment_status == 'pending') {
                    actionButtons += `
                        <button type="button" class="btn btn-sm btn-info payment-status-btn" data-bs-toggle="modal" data-bs-target="#paymentStatusModal" title="Update Payment Status">
                            <i class="fas fa-credit-card"></i>
                        </button>
                    `;
                }

                if (order.payment_status === 'approved' || order.payment_status === 'rejected') {
                    if (order.status !== 'completed' && order.status !== 'cancelled') {
                        actionButtons += `
                            <button type="button" class="btn btn-sm btn-success status-btn" data-bs-toggle="modal" data-bs-target="#statusModal" title="Update Status">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        `;
                    }
                }

                actionButtons += `
                    <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete Order">
                        <i class="fas fa-trash"></i>
                    </button>
                    <form method="POST" action="{{ route('orders.delete', '') }}/${order.order_id}" style="display:none;" class="delete-form">
                        @csrf
                        @method('DELETE')
                    </form>
                `;
                
                const newRow = $(`
                    <tr data-order='${JSON.stringify(order)}' id="order-row-${order.order_id}" class="new-order-highlight">
                        <td>#${order.order_id}</td>
                        <td>${order.user.name}</td>
                        <td><small class="text-muted">${vendorsText}</small></td>
                        <td>${order.building_name}, ${order.room_number}</td>
                        <td><small class="text-muted">${deliveryTimeText}</small></td>
                        <td>${order.courier_name || 'Not assigned'}</td>
                        <td>Rp ${formatNumber(order.total_price)}</td>
                        <td><span class="badge ${statusBadgeClass}">${capitalizeFirst(order.status.replace('_', ' '))}</span></td>
                        <td>${paymentStatusHtml}</td>
                        <td>${actionButtons}</td>
                    </tr>
                `);
                
                ordersDataTable.row.add(newRow[0]).draw(false);
                
                setTimeout(function() {
                    newRow.removeClass('new-order-highlight');
                }, 5000);
            }

            function getStatusBadgeClass(status) {
                switch(status) {
                    case 'completed': return 'bg-success';
                    case 'cancelled': return 'bg-danger';
                    case 'on_delivery': return 'bg-info';
                    case 'processing': return 'bg-warning';
                    case 'pending': return 'bg-secondary';
                    default: return 'bg-secondary';
                }
            }

            function getPaymentStatusBadgeClass(status) {
                switch(status) {
                    case 'approved': return 'bg-success';
                    case 'rejected': return 'bg-danger';
                    case 'pending': return 'bg-warning';
                    default: return 'bg-secondary';
                }
            }

            function capitalizeFirst(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            $('#viewOrderModal').on('hidden.bs.modal', function() {
                $('#delivery-notes-section').hide();
            });

            initializeDataTable();
            initializeEventHandlers();
            startPolling();

            setTimeout(function() {
                $('#errorAlert, #successAlert').alert('close');
            }, 3000);

            $(window).on('beforeunload', function() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
                if (ordersDataTable) {
                    ordersDataTable.destroy();
                }
            });
        });
    </script>

    <style>
        .new-order-highlight {
            animation: highlight 5s ease-out;
        }

        @keyframes highlight {
            0% {
                background-color: rgba(255, 251, 204, 1);
            }
            100% {
                background-color: rgba(255, 251, 204, 0);
            }
        }

        .status-update-highlight {
            animation: status-highlight 3s ease-out;
        }

        @keyframes status-highlight {
            0% {
                background-color: rgba(204, 229, 255, 1);
            }
            100% {
                background-color: rgba(204, 229, 255, 0);
            }
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .dataTables_wrapper .dataTables_length select {
            width: auto;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: auto;
        }

        #ordersTable_wrapper {
            padding: 0;
        }

        .dataTables_info {
            padding-top: 8px;
        }

        .dataTables_paginate {
            padding-top: 8px;
        }

        .form-label {
            font-weight: 500;
        }

        .text-danger {
            font-weight: 600;
        }

        #statusFilter {
            font-size: 0.875rem;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .badge {
            font-size: 0.875rem;
        }

        .text-primary {
            background-color: #e3f2fd;
            padding: 2px 6px;
            border-radius: 4px;
            border-left: 3px solid #2196f3;
            display: inline-block;
            max-width: 200px;
            word-break: break-word;
        }

        @media (max-width: 768px) {
            .text-primary {
                max-width: 150px;
                font-size: 0.75rem;
            }
        }

        .notes-tooltip {
            cursor: help;
            text-decoration: underline;
            text-decoration-style: dotted;
        }
    </style>
@endsection