<!-- resources/views/content/home.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Home')

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Data Pasien</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addEditPasienModal">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <!-- Menampilkan alert untuk kesalahan dan sukses -->
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

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                            <strong>Error!</strong> 
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Jenis Kelamin</th>
                                    <th>NIK</th>
                                    <th>Alamat</th>
                                    <th>Telepon</th>
                                    <th>Email</th>
                                    <th>Tanggal Pendaftaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pasien as $p)
                                    <tr data-pasien="{{ json_encode($p) }}">
                                        <td>{{ $p->nama }}</td>
                                        <td>{{ $p->tanggal_lahir }}</td>
                                        <td>{{ $p->jenis_kelamin }}</td>
                                        <td>{{ $p->nik }}</td>
                                        <td>{{ $p->alamat }}</td>
                                        <td>{{ $p->telepon }}</td>
                                        <td>{{ $p->email }}</td>
                                        <td>{{ $p->tanggal_pendaftaran }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#addEditPasienModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form method="POST" action="{{ route('pasien.destroy', $p->id_pasien) }}"
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

    <!-- Modal untuk tambah/edit data pasien -->
    <div class="modal fade" id="addEditPasienModal" tabindex="-1" aria-labelledby="addEditPasienModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEditPasienModalLabel">Tambah/Edit Data Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('pasien.save') }}" id="pasienForm">
                    @csrf
                    <input type="hidden" name="id_pasien" id="id_pasien">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <input type="text" class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                        </div>
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" required>
                        </div>
                        <div class="mb-3">
                            <label for="telepon" class="form-label">Telepon</label>
                            <input type="text" class="form-control" id="telepon" name="telepon">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_pendaftaran" class="form-label">Tanggal Pendaftaran</label>
                            <input type="date" class="form-control" id="tanggal_pendaftaran"
                                name="tanggal_pendaftaran" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {
            // Reset form ketika modal ditutup
            $('#addEditPasienModal').on('hidden.bs.modal', function() {
                $('#pasienForm')[0].reset();
                $('#id_pasien').val('');
                $('#pasienForm').validate().resetForm(); // Reset form validation
            });

            // Isi form dengan data pasien saat tombol edit diklik
            $('.edit-btn').on('click', function() {
                var row = $(this).closest('tr');
                var pasien = row.data('pasien');

                $('#id_pasien').val(pasien.id_pasien);
                $('#nama').val(pasien.nama);
                $('#tanggal_lahir').val(pasien.tanggal_lahir);
                $('#jenis_kelamin').val(pasien.jenis_kelamin);
                $('#nik').val(pasien.nik);
                $('#alamat').val(pasien.alamat);
                $('#telepon').val(pasien.telepon);
                $('#email').val(pasien.email);
                $('#tanggal_pendaftaran').val(pasien.tanggal_pendaftaran);
            });

            // Menutup alert error dan success setelah 3 detik
            setTimeout(function() {
                $('#errorAlert').alert('close');
                $('#successAlert').alert('close');
            }, 3000);

            // Konfirmasi delete menggunakan SweetAlert2
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                var form = $(this).siblings('.delete-form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Validasi form dengan jQuery Validation
            $('#pasienForm').validate({
                rules: {
                    nama: {
                        required: true,
                        maxlength: 100
                    },
                    tanggal_lahir: {
                        required: true,
                        date: true
                    },
                    jenis_kelamin: {
                        required: true,
                        maxlength: 10
                    },
                    nik: {
                        required: true,
                        digits: true,
                        minlength: 16,
                        maxlength: 16
                    },
                    alamat: {
                        required: true,
                        maxlength: 255
                    },
                    telepon: {
                        maxlength: 20,
                        digits: true
                    },
                    email: {
                        email: true,
                        maxlength: 100
                    },
                    tanggal_pendaftaran: {
                        required: true,
                        date: true
                    }
                },
                messages: {
                    nama: {
                        required: "Nama wajib diisi.",
                        maxlength: "Nama maksimal 100 karakter."
                    },
                    tanggal_lahir: {
                        required: "Tanggal lahir wajib diisi.",
                        date: "Format tanggal tidak valid."
                    },
                    jenis_kelamin: {
                        required: "Jenis kelamin wajib diisi.",
                        maxlength: "Jenis kelamin maksimal 10 karakter."
                    },
                    nik: {
                        required: "NIK wajib diisi.",
                        digits: "NIK harus berupa angka.",
                        minlength: "NIK harus 16 digit.",
                        maxlength: "NIK harus 16 digit."
                    },
                    alamat: {
                        required: "Alamat wajib diisi.",
                        maxlength: "Alamat maksimal 255 karakter."
                    },
                    telepon: {
                        maxlength: "Telepon maksimal 20 karakter.",
                        digits: "Telepon harus berupa angka."
                    },
                    email: {
                        email: "Format email tidak valid.",
                        maxlength: "Email maksimal 100 karakter."
                    },
                    tanggal_pendaftaran: {
                        required: "Tanggal pendaftaran wajib diisi.",
                        date: "Format tanggal tidak valid."
                    }
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        });
    </script>
@endsection
