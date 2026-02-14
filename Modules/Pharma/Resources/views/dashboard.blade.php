@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
    {{ 'Dashboard' }}
@endsection

@section('content')
    <div class="d-flex align-items-center pb-3 pt-3">
        <span class="head-title fw-medium">@lang('messages.main')</span>
        <svg class="mx-2" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
            <g clip-path="url(#clip0_2007_2051)">
                <path d="M2.625 2.25L6.375 6L2.625 9.75" stroke="#828A90" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M6.375 2.25L10.125 6L6.375 9.75" stroke="#828A90" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </g>
            <defs>
                <clipPath id="clip0_2007_2051">
                    <rect width="12" height="12" fill="white" />
                </clipPath>
            </defs>
        </svg>
        <span class="head-title fw-medium h6 mb-0">{{ __('dashboard.title') }}</span>
    </div>
    <div class="user-info mb-50">
        <h1 class="fs-37">
            <span class="left-text text-capitalize fw-light">{{ greeting() }} </span>
            <span class="right-text text-capitalize">{{ $current_user }}</span>
        </h1>

    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">{{ __('pharma::messages.insights') }}</h3>
                <div class="d-flex  align-items-center">

                    <form id="dateRangeForm" class="d-flex align-items-center gap-2">
                        <div class="form-group my-0 ms-3 d-flex gap-3">
                            <input type="text" name="date_range" id="revenuedateRangeInput" value="{{ $date_range }}"
                                class="form-control dashboard-date-range" placeholder="{{ __('messages.Select_Date') }}"
                                readonly="readonly">
                            <a href="{{ route('backend.pharma-dashboard') }}" class="btn btn-primary"
                                id="refreshRevenuechart" title="{{ __('appointment.reset') }}" data-bs-placement="top"
                                data-bs-toggle="tooltip">
                                <i class="ph ph-arrow-counter-clockwise"></i>
                            </a>
                            <button type="submit" name="action" value="filter" class="btn btn-secondary" data-bs-to
                                ggle="tooltip" data-bs-title="{{ __('messages.submit_date_filter') }}" id="submitBtn"
                                disabled>{{ __('dashboard.lbl_submit') }}</button>
                        </div>
                    </form>

                </div>
            </div>
            <div class="row align-items-stretch">
                <!-- Left column -->
                <div class="col-lg-8 d-flex flex-column">

                    <div class="row g-4 mt-1">
                        <div class="col-sm-6 col-lg-4 my-0">
                            <div
                                class="card dashboard-card border-0 hover-shadow transition-all position-relative overflow-hidden">
                                <a href="{{ route('backend.medicine.index') }}" class="stretched-link"></a>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.total_medicine') }}</h6>
                                            <h2 class="mb-0 fw-bold" id="total_medicine_count">
                                                {{ $data['total_medicine'] }}</h2>
                                        </div>
                                        <div class="card-icon bg-primary-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/medicine.png') }}" alt="Medicine"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.top_medicine_count') }}</h6>
                                            <h2 class="mb-0 fw-bold" id="top_medicine_count">{{ $data['top_medicines'] }}
                                            </h2>
                                        </div>
                                        <div class="card-icon bg-success-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/top-meidicne.png') }}" alt="Top Medicine"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.upcoming_medicine_expiry') }}</h6>
                                            <h2 class="mb-0 fw-bold" id="upcoming-medicine-expiry">
                                                {{ $data['upcoming_meidcine_expiry'] }}</h2>
                                        </div>
                                        <div class="card-icon bg-warning-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/expired-medicine.png') }}" alt="Expiry"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.low_stock_medicine') }}</h6>
                                            <h2 class="mb-0 fw-bold">{{ $data['low_stock_meidcine_count'] ?? 0 }}</h2>
                                        </div>
                                        <div class="card-icon bg-danger-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/product.png') }}" alt="Low Stock"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <a href="{{ route('backend.suppliers.index') }}" class="stretched-link"></a>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.total_supplier') }}</h6>
                                            <h2 class="mb-0 fw-bold">{{ $data['total_supplier'] ?? 0 }}</h2>
                                        </div>
                                        <div class="card-icon  bg-info-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/supplier.png') }}" alt="Low Stock"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.total_order') }}</h6>
                                            <h2 class="mb-0 fw-bold">{{ $data['total_orders'] ?? 0 }}</h2>
                                        </div>
                                        <div class="card-icon bg-primary-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/shopping-cart.png') }}" alt="Low Stock"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.withdrawal_amount') }}</h6>
                                            <h2 class="mb-0 fw-bold">{{ Currency::format($data['withdrawal_amount']) }}
                                            </h2>
                                        </div>
                                        <div class="card-icon  bg-success-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/withdrawal.png') }}"
                                                alt="Withdrawal Amount" class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all position-relative">
                                <a href="{{ route('backend.payout.pharma-payout-report') }}" class="stretched-link"></a>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">
                                                {{ __('pharma::messages.total_earnings') }}</h6>
                                            <h2 class="mb-0 fw-bold" id="toal-earning">
                                                {{ Currency::format($data['toal_earnings']) }}
                                            </h2>
                                        </div>
                                        <div class="card-icon  bg-warning-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/profit.png') }}" alt="Earnings"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 my-0">
                            <div class="card dashboard-card border-0 hover-shadow transition-all">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-heading text-uppercase">{{ __('dashboard.total_revenue') }}
                                            </h6>
                                            <h2 class="mb-0 fw-bold" id="total_revenue_amount">
                                                {{ Currency::format($data['total_revenue_generated']) }}
                                            </h2>
                                        </div>
                                        <div class="card-icon bg-danger-subtle p-3 rounded-3">
                                            <img src="{{ asset('img/dashboard/revenue.png') }}" alt="Revenue"
                                                class="img-fluid avatar-50 object-contain ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="col-lg-4 d-flex flex-column">
                    <div class="card card-block card-stretch card-height flex-fill d-flex flex-column">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <h4 class="card-title mb-0">{{ __('pharma::messages.new_prescription') }}</h4>
                            @if (count($data['letest_prescriptions']) >= 3)
                                <a id="letest_prescription_view_all_link"
                                    href="{{ route('backend.prescription.index') }}" class="text-secondary">
                                    {{ __('pharma::messages.view_all') }}
                                </a>
                            @endif
                        </div>
                        <div class="card-body pt-0 pb-2">
                            <div class="letest-prescription">
                                <ul id="letest-prescription" class="list-unstyled p-0 m-0">
                                    @forelse ($data['letest_prescriptions'] as $letest_prescription)
                                        <li class="{{ $loop->last ? 'mb-0' : 'mb-2' }}">
                                            <div class="theme-box pb-3 mb-3 border-bottom">
                                                <div class="row align-items-center gy-1">
                                                    <div class="col-lg-2 col-md-2">
                                                        <div class="position-relative">
                                                            @if (optional($letest_prescription->user)->profile_image)
                                                                <img src="{{ optional($letest_prescription->user)->profile_image }}"
                                                                    alt="{{ optional($letest_prescription->user)->full_name }}"
                                                                    class="rounded-circle avatar-50 object-cover">
                                                            @else
                                                                <div
                                                                    class="rounded-circle bg-light d-flex align-items-center justify-content-center avatar-50 object-cover">
                                                                    <i class="ph ph-user fs-3"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-7">
                                                        <div class="ms-3">
                                                            <h6 class="mb-1 fw-semibold">
                                                                {{ optional($letest_prescription->user)->full_name ?? __('pharma::messages.unknown_patient') }}
                                                            </h6>
                                                            <p class="mb-0 font-size-14">
                                                                {{ date($data['dateformate'], strtotime($letest_prescription->created_at)) }}
                                                                at
                                                                {{ $letest_prescription->created_at ? \Carbon\Carbon::parse($letest_prescription->created_at)->timezone($timeZone)->format($data['timeformate']) : '--' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-3 text-end">
                                                        <a
                                                            href="{{ route('backend.prescription.add_extra_medicine', $letest_prescription->id) }}">
                                                            <button type="button"
                                                                class="btn btn-primary-subtle px-3 py-1">
                                                                {{ __('pharma::messages.add_medicine') }}
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-center py-5">
                                            <div class="text-body">
                                                <i class="ph ph-prescription text-body mb-3" style="font-size: 48px;"></i>
                                                <p class="mb-0">{{ __('dashboard.no_data_available') }}</p>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-xxl-8 col-lg-7 col-md-6">
            <div class="card card-block card-stretch card-height">
                <div id="total-revenue-loading-spinner" class="chart-loader postion-absolute top-50 start-50">
                </div>
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <h4 class="card-title mb-0">{{ __('dashboard.lbl_tot_revenue') }}</h4>
                    <div id="date_range" class="dropdown d-none">

                        <a href="#" class="dropdown-toggle btn text-body bg-body border total_revenue"
                            id="dropdownTotalRevenue" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __('pharma::messages.year') }}
                            <svg width="8" class="ms-1 transform-up" viewBox="0 0 12 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M6 5.08579L10.2929 0.792893C10.6834 0.402369 11.3166 0.402369 11.7071 0.792893C12.0976 1.18342 12.0976 1.81658 11.7071 2.20711L6.70711 7.20711C6.31658 7.59763 5.68342 7.59763 5.29289 7.20711L0.292893 2.20711C-0.0976311 1.81658 -0.0976311 1.18342 0.292893 0.792893C0.683418 0.402369 1.31658 0.402369 1.70711 0.792893L6 5.08579Z"
                                    fill="currentColor"></path>
                            </svg>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-soft-primary sub-dropdown"
                            aria-labelledby="dropdownTotalRevenue">
                            <li><a class="revenue-dropdown-item dropdown-item"
                                    data-type="Year">{{ __('pharma::messages.yearly') }}</a></li>
                            <li><a class="revenue-dropdown-item dropdown-item"
                                    data-type="Month">{{ __('pharma::messages.monthly') }}</a></li>
                            <li><a class="revenue-dropdown-item dropdown-item"
                                    data-type="Week">{{ __('pharma::messages.weekly') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="total-revenue"></div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-lg-5 col-md-6">
            <div class="card card-block card-stretch card-height">
                <div id="meidcine-user-loading-spinner" class="chart-loader postion-absolute top-50 start-50">
                </div>
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <h4 class="card-title mb-0">{{ __('pharma::messages.meidicne_usage') }}</h4>
                    <select id="usage_filter" class="form-select w-auto">
                        <option value="weekly">{{ __('pharma::messages.weekly') }}</option>
                        <option value="monthly">{{ __('pharma::messages.monthly') }}</option>
                    </select>
                </div>
                <div class="card-body pt-0">
                    <div id="medicine-usage-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-lg-5 col-md-6">
            <div class="card card-block card-stretch card-height">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">{{ __('pharma::messages.top_supplier') }}</h5>

                    @if (!$data['top_suppliers']->isEmpty())
                        <a href="{{ route('backend.suppliers.index') }}"
                            class="view-all-btn">{{ __('pharma::messages.view_all') }}</a>
                    @endif
                </div>

                <div class="supplier-list">
                    @if ($data['top_suppliers']->isEmpty())
                        <div class="d-flex flex-column justify-content-center align-items-center text-body text-center">
                            <i class="ph ph-prescription text-body mb-3" style="font-size: 48px;"></i>
                            <p class="mb-0">{{ __('dashboard.no_data_available') }}</p>
                        </div>
                    @else
                        @foreach ($data['top_suppliers'] as $supplier)
                            <div class="supplier-item">
                                <div class="supplier-info">
                                    <h6 class="supplier-name text-dark">{{ $supplier['full_name'] }}</h6>
                                    <p class="supplier-units">{{ $supplier['purchase_count'] }}
                                        {{ __('pharma::messages.unit') }}</p>
                                </div>
                                <span class="badge {{ $supplier['status'] == '1' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $supplier['status'] == '1' ? __('messages.active') : __('messages.deactive') }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
        <div class="col-xxl-3 col-lg-5 col-md-6">
            <div class="card card-block card-stretch card-height">
                <div class="card-header">
                    <h5 class="chart-title mb-0">{{ __('pharma::messages.stock_overview') }}</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div id="stockChart"></div>
                        <div class="total-count">
                            <div class="total-number">
                                {{ $data['low_stock'] + $data['expired_in_stock'] + $data['available_medicines'] }}
                            </div>
                            <div class="total-label">
                                {{ __('pharma::messages.total_items') }}
                            </div>
                        </div>

                    </div>
                    <div class="legend-container">
                        <div class="legend-item">
                            <div class="legend-color in-stock"></div>
                            <span>{{ __('pharma::messages.in_stock') }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color low-stock"></div>
                            <span>{{ __('pharma::messages.low_stock') }}</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color expired"></div>
                            <span>{{ __('pharma::messages.expired') }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xxl-6 col-lg-5 col-md-6">
            <div class="card card-block card-stretch card-height position-relative">
                <div id="earning-loading-spinner" class="chart-loader postion-absolute top-50 start-50">
                </div>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h5 class="chart-title">{{ __('pharma::messages.total_earnings') }}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <select class="form-select form-select-sm" id="earnings_filter" style="width: auto;">
                            <option value="weekly">{{ __('pharma::messages.weekly') }}</option>
                            <option value="monthly" selected> {{ __('pharma::messages.monthly') }}</option>
                            <option value="yearly">{{ __('pharma::messages.yearly') }}</option>
                        </select>
                    </div>
                </div>
                <div class="card-body position-relative" style="min-height: 300px;">
                    <div id="earnings-chart-wrapper" class="position-relative" style="height: 300px;">
                        <div id="earnings-chart" style="height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
    <style>
        .list-group {
            --bs-list-group-item-padding-y: 1.5rem;
            --bs-list-group-color: inherit !important;
        }

        .date-calender {
            display: flex;
            justify-content: space-between;
        }

        .date-calender .date {
            width: 12%;
            display: flex;
            align-items: center;
            flex-direction: column
        }

        .letest-prescription {
            min-height: 23.5rem;
            max-height: 23.5rem;
            overflow-y: scroll;
        }

        .register-vendors-list {
            height: 22rem;
            overflow-y: auto;
        }

        .iq-upcomming {
            display: flex !important;
            justify-content: center;
            align-items: center;
        }

        .letest-prescription .bg-white:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            transition: box-shadow 0.3s ease;
        }

        .letest-prescription .btn-outline-primary {
            border-color: #6366f1;
            color: #6366f1;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .letest-prescription .btn-outline-primary:hover {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        .letest-prescription h5 {
            font-size: 1.25rem;
            color: #1f2937;
        }

        .letest-prescription .text-primary {
            color: #6366f1 !important;
        }

        .letest-prescription .shadow-sm {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06) !important;
        }

        .letest-prescription .rounded-4 {
            border-radius: 1rem !important;
        }

        .letest-prescription .border-light {
            border-color: #e5e7eb !important;
        }

        .view-all-btn {
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .view-all-btn:hover {
            color: #e55a5a;
            text-decoration: none;
        }

        .supplier-list {
            padding: 0 24px 24px 24px;
        }

        .supplier-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .supplier-item:last-child {
            border-bottom: none;
        }

        .supplier-info h6 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
        }

        .supplier-units {
            font-size: 14px;
            color: #6c7ae0;
            font-weight: 500;
            margin: 0;
        }

        .status-badge {
            background-color: #e8f5e8;
            color: #4caf50;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            border: none;
        }

        .chart-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .total-count {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
            z-index: 10;
        }

        .total-number {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
        }

        .total-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 4px;
        }

        .legend-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #2c3e50;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .legend-color.in-stock {
            background-color: #4f46e5;
        }

        .legend-color.low-stock {
            background-color: #8b5cf6;
        }

        .legend-color.expired {
            background-color: #c7d2fe;
        }

        #earning-loading-spinner,
        #meidcine-user-loading-spinner,
        #total-revenue-loading-spinner {
            width: 20px;
            height: 20px;
            border: 3px solid #ccc;
            border-top: 3px solid #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .chart-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        body.dark-mode .supplier-name {
            color: white;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/apexcharts/css/apexcharts.min.css') }}" />
@endpush
@push('after-scripts')
    <script src="{{ asset('vendor/apexcharts/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('vendor/moment/moment.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('revenuedateRangeInput');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('dateRangeForm');

            function isValidDateRange(dateRange) {
                const datePattern = /^\d{4}-\d{2}-\d{2} to \d{4}-\d{2}-\d{2}$/;
                return datePattern.test(dateRange.trim());
            }

            function toggleSubmitButton() {
                if (isValidDateRange(dateInput.value)) {
                    submitBtn.removeAttribute('disabled');
                } else {
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            }

            dateInput.addEventListener('input', toggleSubmitButton);

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                if (isValidDateRange(dateInput.value)) {
                    const encodedDateRange = encodeURIComponent(dateInput.value);
                    const formAction = `{{ url('app/pharma-dashboard/daterange') }}/${encodedDateRange}`;
                    window.location.href = formAction;
                }
            });

            toggleSubmitButton();
            setChartRange('weekly');
            Scrollbar.init(document.querySelector('.letest-prescription'), {
                continuousScrolling: false,
                alwaysShowTracks: false
            })
            const range_flatpicker = document.querySelectorAll('.dashboard-date-range');
            Array.from(range_flatpicker, (elem) => {
                if (typeof flatpickr !== typeof undefined) {
                    flatpickr(elem, {
                        mode: "range",
                    })
                }
            })

            setEarningsChartRange('monthly');
        });



        var dateRangeValue = $('#revenuedateRangeInput').val();



        if (dateRangeValue != '') {
            var dates = dateRangeValue.split(" - ");
            var startDate = dates[0];
            var endDate = dates[1];
            console.log(startDate, endDate);
            if (startDate != null && endDate != null) {
                revanue_chart('Free', startDate, endDate);
                $('#refreshRevenuechart').removeClass('d-none');
                $('#date_range').addClass('d-none');
            }
        } else {
            revanue_chart('Year');
            $('#refreshRevenuechart').addClass('d-none');
            $('#date_range').removeClass('d-none');
        }

        $('#refreshRevenuechart').on('click', function() {
            $('#revenuedateRangeInput').val('');
            revanue_chart('Year');
            $('#date_range').removeClass('d-none');
        });



        var chart = null;
        let revenueInstance;

        function revanue_chart(type, startDate, endDate) {
            var Base_url = "{{ url('/') }}";
            var url = Base_url + "/app/pharma/get_revnue_chart_data/" + type;

            $("#revenue_loader").show();

            const revenueTranslations = {
                Year: @json(__('dashboard.year')),
                Month: @json(__('dashboard.month')),
                Week: @json(__('dashboard.week'))
            };
            $.ajax({
                url: url,
                method: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    $("#total-revenue-loading-spinner").addClass('d-none');
                    $("#revenue_loader").hide();
                    $(".total_revenue").text(revenueTranslations[type]);
                    console.log(response);
                    if (document.querySelectorAll('#total-revenue').length) {
                        const variableColors = IQUtils.getVariableColor();
                        const colors = [variableColors.primary, variableColors.info];

                        let monthlyTotals = [];
                        let category = [];

                        if (type === 'Year') {
                            monthlyTotals = response.data.year_chart_data;
                            category = response.data.month_names;
                        } else if (type === 'Month') {
                            monthlyTotals = response.data.month_chart_data;
                            category = response.data.weekNames;
                        } else if (type === 'Week') {
                            monthlyTotals = response.data.week_chart_data;
                            category = response.data.dayNames;
                        } else if (type === 'Free') {
                            monthlyTotals = response.data.custom_chart_data;
                            category = response.data.custom_categories;
                        }

                        const options = {
                            series: [{
                                name: "{{ __('messages.total_revenue') }}",
                                data: monthlyTotals
                            }],
                            chart: {
                                fontFamily: '"Inter", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
                                height: 300,
                                type: 'area',
                                toolbar: {
                                    show: false
                                },
                                sparkline: {
                                    enabled: false,
                                },
                            },
                            colors: colors,
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 3,
                            },
                            yaxis: {
                                show: true,
                                labels: {
                                    show: true,
                                    style: {
                                        colors: "#8A92A6",
                                    },
                                    offsetX: -15,
                                    formatter: function(value) {
                                        return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ GetcurrentCurrency() }}'
                                        }).format(value);
                                    }
                                },
                            },
                            legend: {
                                show: false,
                            },
                            xaxis: {
                                labels: {
                                    minHeight: 22,
                                    maxHeight: 22,
                                    show: true,
                                },
                                lines: {
                                    show: false
                                },
                                categories: category
                            },
                            grid: {
                                show: true,
                                borderColor: 'var(--bs-body-bg)',
                                strokeDashArray: 0,
                                position: 'back',
                                xaxis: {
                                    lines: {
                                        show: true
                                    }
                                },
                                yaxis: {
                                    lines: {
                                        show: true
                                    }
                                },
                            },
                            fill: {
                                type: 'solid',
                                opacity: 0
                            },
                            tooltip: {
                                enabled: true,
                                y: {
                                    formatter: function(value) {
                                        return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ GetcurrentCurrency() }}'
                                        }).format(value);
                                    }
                                }
                            },
                        };

                        if (revenueInstance) {
                            revenueInstance.updateOptions(options);
                        } else {
                            revenueInstance = new ApexCharts(document.querySelector("#total-revenue"), options);
                            revenueInstance.render();
                        }
                    }
                }
            });
        }


        $(document).on('click', '.revenue-dropdown-item', function() {
            var type = $(this).data('type');
            $('#revenuedateRangeInput').val('');
            revanue_chart(type);
        });

        let medicineUsageChartInstance;

        function loadMedicineUsageChart(startDate = null, endDate = null, type = 'weekly') {
            $.ajax({
                url: "{{ route('backend.pharma.medicine-usage-chart') }}",
                method: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    type: type
                },
                success: function(response) {
                    $("#meidcine-user-loading-spinner").addClass('d-none');
                    const data = response.data;
                    const categories = response.categories;

                    const options = {
                        series: data,
                        chart: {
                            type: 'bar',
                            height: 300,
                            stacked: true, // Changed back to true for stacked bars
                            toolbar: {
                                show: false
                            },
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                borderRadius: 4,
                                columnWidth: '50%',
                                dataLabels: {
                                    position: 'center'
                                }
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        xaxis: {
                            categories: categories,
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    colors: '#8e8da4'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (val) => {
                                    if (val >= 1000) {
                                        return (val / 1000).toFixed(0) + 'k';
                                    }
                                    return parseInt(val);
                                },
                                style: {
                                    fontSize: '12px',
                                    colors: '#8e8da4'
                                }
                            },
                            tickAmount: 4
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            floating: false,
                            fontSize: '12px',
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 2
                            },
                            itemMargin: {
                                horizontal: 15
                            }
                        },
                        fill: {
                            opacity: 0.9
                        },
                        tooltip: {
                            y: {
                                formatter: (val) => `${val.toLocaleString()} uses`
                            },
                            style: {
                                fontSize: '12px'
                            }
                        },
                        colors: ['#FF6B7A', '#FFB4B4', '#E8C4C4'], // Different shades for stacked segments
                        grid: {
                            borderColor: '#f1f1f1',
                            strokeDashArray: 3,
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        }
                    };

                    if (medicineUsageChartInstance) {
                        medicineUsageChartInstance.updateOptions(options);
                    } else {
                        medicineUsageChartInstance = new ApexCharts(document.querySelector(
                            "#medicine-usage-chart"), options);
                        medicineUsageChartInstance.render();
                    }
                },
                error: function(xhr, status, error) {
                    $('#earning-loading-spinner').addClass('d-none');
                    console.error('Error loading medicine usage chart:', error);
                }
            });
        }

        function setChartRange(range) {
            console.log(dateRangeValue);
            if (dateRangeValue != '') {
                var dates = dateRangeValue.split(" - ");
                var startDate = dates[0];
                var endDate = dates[1];

                if (startDate && endDate) {
                    loadMedicineUsageChart(startDate, endDate, 'free'); // fixed
                }
            } else if (range === 'weekly') {
                const start = moment().startOf('week').format('YYYY-MM-DD');
                const end = moment().endOf('week').format('YYYY-MM-DD');
                loadMedicineUsageChart(start, end, 'weekly');
            } else if (range === 'monthly') {
                const start = moment().startOf('month').format('YYYY-MM-DD');
                const end = moment().endOf('month').format('YYYY-MM-DD');
                loadMedicineUsageChart(start, end, 'monthly');
            }
        }

        $('#usage_filter').on('change', function() {
            const selected = $(this).val();
            setChartRange(selected);
        });


        const options = {
            series: [{{ $data['available_medicines'] ?? 0 }}, {{ $data['low_stock'] ?? 0 }},
                {{ $data['expired_in_stock'] ?? 0 }}
            ],
            chart: {
                type: 'donut',
                height: 280,
                width: 280,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            colors: ['#4f46e5', '#8b5cf6', '#c7d2fe'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        background: 'transparent',
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            },
            tooltip: {
                enabled: true,
                custom: function({
                    series,
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    const labels = ['In Stock', 'Low Stock', 'Expired'];
                    const value = series[seriesIndex];
                    const percentage = ((value / series.reduce((a, b) => a + b, 0)) * 100).toFixed(1);

                    return `
                        <div style="padding: 8px 12px; background: white; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                                ${labels[seriesIndex]}
                            </div>
                            <div style="color: #7f8c8d; font-size: 13px;">
                                ${value} items (${percentage}%)
                            </div>
                        </div>
                    `;
                }
            },
            stroke: {
                show: false
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: '100%',
                        height: 250
                    }
                }
            }]
        };

        const stockChart = new ApexCharts(document.querySelector("#stockChart"), options);
        stockChart.render();

        function updateTotalCount() {
            const total = options.series.reduce((sum, value) => sum + value, 0);
            document.querySelector('.total-number').textContent = total;
        }

        updateTotalCount();

        function updateChartData(inStock, lowStock, expired) {
            const newSeries = [inStock, lowStock, expired];
            stockChart.updateSeries(newSeries);

            const total = newSeries.reduce((sum, value) => sum + value, 0);
            document.querySelector('.total-number').textContent = total;
        }


        let earningsChartInstance;

        function loadEarningsChart(startDate = null, endDate = null, type = 'monthly') {
            $.ajax({
                url: "{{ route('backend.pharma.earnings-chart') }}",
                method: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    type: type
                },
                beforeSend: function() {
                    $('#earning-loading-spinner').removeClass('d-none');
                },
                success: function(response) {
                    $('#earning-loading-spinner').addClass('d-none');
                    if (response.status) {
                        const data = response.data;
                        const categories = response.categories;
                        const totalEarnings = response.total_earnings;
                        const currencySymbol = response.currency_symbol || '$';
                        if (!isNaN(totalEarnings) && totalEarnings > 0) {
                            $('#totalEarningDisplay').text(currencySymbol + parseFloat(totalEarnings)
                                .toLocaleString('en-IN', {
                                    minimumFractionDigits: 2
                                }));
                        }

                        const maxValues = Math.max(...data);
                        const yAxisMax = maxValues > 0 ? undefined : 10;


                        const options = {
                            series: [{
                                name: 'Earnings',
                                data: data
                            }],
                            chart: {
                                type: 'bar',
                                height: 320,
                                toolbar: {
                                    show: false
                                },
                                background: 'transparent'
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    columnWidth: '45%',
                                    dataLabels: {
                                        position: 'top'
                                    }
                                }
                            },
                            colors: ['#8b5cf6'],
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                show: true,
                                width: 0,
                                colors: ['transparent']
                            },
                            xaxis: {
                                categories: categories,
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                                labels: {
                                    style: {
                                        colors: '#6b7280',
                                        fontSize: '12px'
                                    }
                                }
                            },
                            yaxis: {
                                min: 0,
                                max: yAxisMax,
                                labels: {
                                    formatter: function(value) {
                                        return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                            style: 'currency',
                                            currency: '{{ GetcurrentCurrency() }}'
                                        }).format(value);
                                    },

                                    style: {
                                        colors: '#6b7280',
                                        fontSize: '12px'
                                    }
                                }
                            },
                            fill: {
                                opacity: 1,
                                colors: ['#8b5cf6']
                            },
                            tooltip: {

                                y: {
                                    formatter: function(val) {
                                        return currencySymbol + val.toLocaleString('en-IN', {
                                            minimumFractionDigits: 2
                                        });
                                    }
                                },
                                marker: {
                                    show: false
                                }
                            },
                            grid: {
                                borderColor: '#f3f4f6',
                                strokeDashArray: 3,
                                xaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                                yaxis: {
                                    lines: {
                                        show: true
                                    }
                                }
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        height: 280
                                    },
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '60%'
                                        }
                                    }
                                }
                            }]
                        };

                        if (earningsChartInstance) {
                            earningsChartInstance.updateOptions(options);
                        } else {
                            earningsChartInstance = new ApexCharts(document.querySelector("#earnings-chart"),
                                options);
                            earningsChartInstance.render();
                        }

                        const maxValue = Math.max(...data);
                        if (maxValue > 0) {
                            setTimeout(() => {
                                earningsChartInstance.updateOptions({
                                    plotOptions: {
                                        bar: {
                                            colors: {
                                                ranges: [{
                                                    from: maxValue - 1,
                                                    to: maxValue + 1,
                                                    color: '#4f46e5'
                                                }]
                                            }
                                        }
                                    }
                                });
                            }, 100);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#earning-loading-spinner').addClass('d-none');
                    console.error('Error loading earnings chart:', error);
                }
            });
        }

        function setEarningsChartRange(range) {
            console.log(dateRangeValue);
            if (dateRangeValue != '') {
                var dates = dateRangeValue.split(" - ");
                var startDate = dates[0];
                var endDate = dates[1];

                if (startDate && endDate) {
                    loadEarningsChart(startDate, endDate, 'custom'); // mark as custom
                }
            } else if (range === 'weekly') {
                const start = moment().startOf('week').format('YYYY-MM-DD');
                const end = moment().endOf('week').format('YYYY-MM-DD');
                loadEarningsChart(start, end, 'weekly');
            } else if (range === 'monthly') {
                const end = moment().endOf('month').format('YYYY-MM-DD');
                const start = moment().subtract(11, 'months').startOf('month').format('YYYY-MM-DD');
                loadEarningsChart(start, end, 'monthly');
            } else if (range === 'yearly') {
                const start = moment().startOf('year').format('YYYY-MM-DD');
                const end = moment().endOf('year').format('YYYY-MM-DD');
                loadEarningsChart(start, end, 'yearly');
            }
        }

        $('#earnings_filter').on('change', function() {
            const selected = $(this).val();
            setEarningsChartRange(selected);
        });
    </script>
@endpush
