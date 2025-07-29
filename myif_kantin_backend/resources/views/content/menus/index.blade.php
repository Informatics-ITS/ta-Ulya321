@extends('layouts.app')

@section('title', 'Menus Management')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Data Menus</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addEditMenuModal">
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
                                    <th>Vendor</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Photo</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menus as $menu)
                                    <tr data-menu="{{ json_encode($menu) }}">
                                        <td>{{ $menu->vendor->name }}</td>
                                        <td>{{ $menu->name }}</td>
                                        <td>{{ $menu->description }}</td>
                                        <td>
                                            @if($menu->photo_url)
                                                <img src="{{ asset($menu->photo_url) }}" alt="{{ $menu->name }}" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">No photo</span>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $menu->is_available ? 'success' : 'danger' }}">
                                                {{ $menu->is_available ? 'Available' : 'Unavailable' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#addEditMenuModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form method="POST" action="{{ route('menus.delete', $menu->menu_id) }}"
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

    <!-- Modal for add/edit menu -->
    <div class="modal fade" id="addEditMenuModal" tabindex="-1" aria-labelledby="addEditMenuModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEditMenuModalLabel">Add/Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('menus.save') }}" id="menuForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="menu_id" id="menu_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-select" id="vendor_id" name="vendor_id" required>
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->vendor_id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="menu_image" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="menu_image" name="menu_image" accept="image/*">
                            <div id="image_preview_container" class="mt-2" style="display: none;">
                                <p>Current Image:</p>
                                <img id="image_preview" src="" alt="Menu Image" style="max-width: 100%; max-height: 200px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_available" name="is_available" value="1" checked>
                                <label class="form-check-label" for="is_available">
                                    Available
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
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
            $('#addEditMenuModal').on('hidden.bs.modal', function() {
                $('#menuForm')[0].reset();
                $('#menu_id').val('');
                $('#image_preview_container').hide();
            });

            $('.edit-btn').on('click', function() {
                var row = $(this).closest('tr');
                var menu = row.data('menu');

                $('#menu_id').val(menu.menu_id);
                $('#vendor_id').val(menu.vendor_id);
                $('#name').val(menu.name);
                $('#description').val(menu.description);
                $('#price').val(menu.price);
                $('#is_available').prop('checked', menu.is_available);
                
                // Show current image if exists
                if (menu.photo_url) {
                    $('#image_preview').attr('src', menu.photo_url);
                    $('#image_preview_container').show();
                } else {
                    $('#image_preview_container').hide();
                }
            });

            // Show image preview when a new file is selected
            $('#menu_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image_preview').attr('src', e.target.result);
                        $('#image_preview_container').show();
                    }
                    reader.readAsDataURL(file);
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
                    text: "This action cannot be undone!",
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
        });
    </script>
@endsection