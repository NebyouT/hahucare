@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
    {{ 'Lab Technician Dashboard' }}
@endsection

@section('content')
    <div class="user-info mb-50">
        <h1 class="fs-37">
            <span class="left-text text-capitalize fw-light">{{ greeting() }} </span>
            <span class="right-text text-capitalize">{{ $current_user }}</span>
        </h1>
        <p class="text-muted mb-0">Lab: {{ $data['lab_name'] ?? 'N/A' }} ({{ $data['lab_code'] ?? 'N/A' }})</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">{{ __('dashboard.lbl_performance') }}</h3>
            </div>
            <div class="row g-4 mb-5">
                <!-- Lab Orders Card -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card dashboard-card border-0 hover-shadow transition-all position-relative mb-0">
                        <a href="{{ route('backend.lab-orders.index') }}" class="stretched-link"></a>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-heading text-uppercase">
                                        {{ __('dashboard.total_lab_orders') }}
                                    </h6>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $data['total_lab_orders'] ?? 0 }}
                                    </h2>
                                </div>
                                <div class="card-icon bg-primary-subtle p-3 rounded-3">
                                    <img src="{{ asset('img/dashboard/clender.png') }}" alt="Lab Orders"
                                        class="img-fluid avatar-50 object-contain">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lab Tests Card -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card dashboard-card border-0 hover-shadow transition-all position-relative mb-0">
                        <a href="{{ route('backend.lab-tests.index') }}" class="stretched-link"></a>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-heading text-uppercase">
                                        {{ __('dashboard.total_lab_tests') }}
                                    </h6>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $data['total_lab_tests'] ?? 0 }}
                                    </h2>
                                </div>
                                <div class="card-icon bg-primary-subtle p-3 rounded-3">
                                    <img src="{{ asset('img/dashboard/services.png') }}" alt="Lab Tests"
                                        class="img-fluid avatar-50 object-contain">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lab Results Card -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card dashboard-card border-0 hover-shadow transition-all position-relative mb-0">
                        <a href="{{ route('backend.lab-results.index') }}" class="stretched-link"></a>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-heading text-uppercase">
                                        {{ __('dashboard.total_lab_results') }}
                                    </h6>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $data['total_lab_results'] ?? 0 }}
                                    </h2>
                                </div>
                                <div class="card-icon bg-primary-subtle p-3 rounded-3">
                                    <img src="{{ asset('img/dashboard/file-text.png') }}" alt="Lab Results"
                                        class="img-fluid avatar-50 object-contain">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders Card -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card dashboard-card border-0 hover-shadow transition-all position-relative mb-0">
                        <a href="{{ route('backend.lab-orders.index') }}?status=pending" class="stretched-link"></a>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-heading text-uppercase">
                                        {{ __('dashboard.pending_orders') }}
                                    </h6>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $data['pending_orders'] ?? 0 }}
                                    </h2>
                                </div>
                                <div class="card-icon bg-warning-subtle p-3 rounded-3">
                                    <img src="{{ asset('img/dashboard/users.png') }}" alt="Pending Orders"
                                        class="img-fluid avatar-50 object-contain">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Orders Card -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card dashboard-card border-0 hover-shadow transition-all position-relative mb-0">
                        <a href="{{ route('backend.lab-orders.index') }}?status=completed" class="stretched-link"></a>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="text-heading text-uppercase">
                                        {{ __('dashboard.completed_orders') }}
                                    </h6>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $data['completed_orders'] ?? 0 }}
                                    </h2>
                                </div>
                                <div class="card-icon bg-success-subtle p-3 rounded-3">
                                    <img src="{{ asset('img/dashboard/total_doctor.png') }}" alt="Completed Orders"
                                        class="img-fluid avatar-50 object-contain">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
