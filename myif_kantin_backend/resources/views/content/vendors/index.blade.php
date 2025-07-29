@extends('layouts.app')

@section('title', 'Vendors Management')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Data Vendors</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addEditVendorModal">
                            <i class="fas fa-plus"></i>
                        </button>
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

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>QRIS Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $vendor)
                                    <tr data-vendor="{{ json_encode($vendor, JSON_FORCE_OBJECT) }}">
                                        <td>{{ $vendor->name }}</td>
                                        <td>{{ $vendor->description }}</td>
                                        <td>
                                            @if($vendor->qris_image)
                                                <button type="button" class="btn btn-sm btn-info view-qris-btn" 
                                                    data-qris="{{ $vendor->qris_image_url }}" 
                                                    data-vendor="{{ $vendor->name }}"
                                                    data-bs-toggle="modal" data-bs-target="#qrisModal">
                                                    <i class="fas fa-qrcode"></i> View QRIS
                                                </button>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-exclamation-triangle"></i> No QRIS
                                                </span>
                                            @endif
                                        </td>
                                        <td class="STATUS">
                                            <span class="badge bg-{{ $vendor->is_active ? 'success' : 'danger' }}">
                                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#addEditVendorModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form method="POST" action="{{ route('vendors.delete', $vendor->vendor_id) }}"
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

    <div class="modal fade" id="addEditVendorModal" tabindex="-1" aria-labelledby="addEditVendorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEditVendorModalLabel">Add/Edit Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('vendors.save') }}" id="vendorForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="vendor_id" id="vendor_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="qris_image" class="form-label">QRIS Payment Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="qris_image" name="qris_image" accept="image/*">
                            <small class="text-muted">Upload QRIS code image for customer payments (Max: 2MB, Format: JPG, PNG, GIF)</small>
                            <div id="current-qris" style="display: none;" class="mt-2">
                                <small class="text-info">Current QRIS:</small>
                                <div class="mt-1">
                                    <img id="current-qris-preview" src="" alt="Current QRIS" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Active Status
                                </label>
                                <small class="form-text text-muted d-block">Inactive vendors will not appear in order creation</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrisModalLabel">QRIS Payment Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h6 id="qris-vendor-name" class="mb-3 text-primary"></h6>
                    <div class="qris-container">
                        <img id="qris-image" src="" alt="QRIS Code" class="img-fluid border rounded" style="max-width: 100%; max-height: 500px;">
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Scan this QRIS code to make payment</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#addEditVendorModal').on('hidden.bs.modal', function() {
                $('#vendorForm')[0].reset();
                $('#vendor_id').val('');
                $('#current-qris').hide();
            });

            $('.edit-btn').on('click', function() {
                var row = $(this).closest('tr');
                var vendor = row.data('vendor');

                $('#vendor_id').val(vendor.vendor_id);
                $('#name').val(vendor.name);
                $('#description').val(vendor.description);
                $('#is_active').prop('checked', vendor.is_active === true || vendor.is_active === 1);
                
                if (vendor.qris_image) {
                    $('#current-qris').show();
                    $('#current-qris-preview').attr('src', vendor.qris_image_url);
                } else {
                    $('#current-qris').hide();
                }
            });

            $('.view-qris-btn').on('click', function() {
                var qrisUrl = $(this).data('qris');
                var vendorName = $(this).data('vendor');
                
                $('#qris-vendor-name').text(vendorName + ' - Payment QRIS');
                $('#qris-image').attr('src', qrisUrl);
            });

            $('#vendorForm').on('submit', function(e) {
                const vendorId = $('#vendor_id').val();
                const qrisFile = $('#qris_image')[0].files[0];
                
                if (!vendorId && !qrisFile) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'QRIS Required',
                        text: 'Please upload a QRIS image for new vendors',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
            });

            setTimeout(function() {
                $('#errorAlert').alert('close');
                $('#successAlert').alert('close');
            }, 3000);

            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                var form = $(this).siblings('.delete-form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete the vendor and all associated data. This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    <style>
        .qris-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .table th {
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.875rem;
        }
        
        .form-label {
            font-weight: 500;
        }
        
        .text-danger {
            font-weight: 600;
        }
    </style>
@endsection