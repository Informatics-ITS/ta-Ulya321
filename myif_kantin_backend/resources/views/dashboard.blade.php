@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12 col-xl-12 stretch-card">
        <div class="row flex-grow-1">
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h6 class="card-title mb-0">Total Students</h6>
                            <div class="dropdown mb-2">
                                <a type="button" href="{{ route('siswa.index') }}">
                                    <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-12 col-xl-5">
                                <h3 class="mb-2">{{ $stats['siswa_count'] }}</h3>
                                <div class="d-flex align-items-baseline">
                                    <p class="text-success">
                                        <span>Students</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-12 col-xl-7">
                                <div class="mt-md-3 mt-xl-0">
                                    <div class="d-flex align-items-center">
                                        <span class="text-primary">
                                            <i data-feather="users" class="icon-lg mb-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h6 class="card-title mb-0">Total Teachers</h6>
                            <div class="dropdown mb-2">
                                <a type="button" href="{{ route('guru.index') }}">
                                    <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-12 col-xl-5">
                                <h3 class="mb-2">{{ $stats['guru_count'] }}</h3>
                                <div class="d-flex align-items-baseline">
                                    <p class="text-danger">
                                        <span>Teachers</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-12 col-xl-7">
                                <div class="mt-md-3 mt-xl-0">
                                    <div class="d-flex align-items-center">
                                        <span class="text-danger">
                                            <i data-feather="user-check" class="icon-lg mb-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h6 class="card-title mb-0">Total Subjects</h6>
                            <div class="dropdown mb-2">
                                <a type="button" href="{{ route('subjects.index') }}">
                                    <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-12 col-xl-5">
                                <h3 class="mb-2">{{ $stats['subject_count'] }}</h3>
                                <div class="d-flex align-items-baseline">
                                    <p class="text-warning">
                                        <span>Subjects</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-12 col-xl-7">
                                <div class="mt-md-3 mt-xl-0">
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning">
                                            <i data-feather="book" class="icon-lg mb-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Welcome, {{ session('user.name') }}!</h4>
                <p class="text-muted mb-4">
                    You are logged in as <strong>{{ session('user.role') }}</strong>. 
                    Use the menu on the left to navigate through the system.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        feather.replace();
    });
</script>
@endpush
