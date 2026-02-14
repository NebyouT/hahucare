<div class="card mb-5">
    <div class="card-body p-5">
        <div class="row align-items-center gy-3 mb-3">
            <div class="col-lg-2 col-md-3">
                <img src="{{ $supplier->profile_image ?? asset('img/default.webp') }}" class="img-fluid avatar avatar-80 avatar-rounded" alt="Supplier Image">
            </div>
            <div class="col-lg-10 col-md-9">
                <h5 class="font-size-18">{{ $supplier->first_name }} {{ $supplier->last_name }}</h5>
                <div class="d-flex align-items-center gap-4 mb-2">
                    <a href="mailto:{{ $supplier->email }}" class="text-decoration-none d-flex align-items-center gap-2 text-dark text-secondary">
                        <i class="ph ph-envelope h5 m-0"></i>
                        <span class="text-secondary text-decoration-underline fw-medium font-size-16">{{ $supplier->email }}</span>
                    </a>
                    <a href="tel:{{ $supplier->contact_number }}" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
                        <i class="ph ph-phone h5 m-0"></i>
                        <span class="text-primary text-decoration-underline fw-medium font-size-16">{{ $supplier->contact_number }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row gy-2">
            <div class="col-lg-2 col-md-3">
                <span>{{ __('pharma::messages.supplier_type') }}:</span>
                <p class="mt-2 h6 mb-0">{{ $supplier->supplierType->name ?? '-' }}</p>
            </div>
            <div class="col-lg-2 col-md-3">
                <span>{{ __('pharma::messages.payment_terms') }}</span>
                <p class="mt-2 h6 mb-0">{{ $supplier->payment_terms }} {{ __('pharma::messages.days') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Pharma Details Section -->
@if($supplier->pharmaUser)
<h5>{{ __('pharma::messages.pharma_info') }}</h5>
<div class="card">
    <div class="card-body p-5">
       <div class="row align-items-center gy-3 mb-3">
            <div class="col-lg-2 col-md-3">
                <img src="{{ $supplier->pharmaUser->profile_image ?? asset('img/default.webp') }}" class="img-fluid avatar avatar-60 avatar-rounded" alt="Pharma Image">
            </div>
            <div class="col-lg-10 col-md-9">
                <h5>{{ $supplier->pharmaUser->first_name }} {{ $supplier->pharmaUser->last_name }}</h5>
                <div class="d-flex flex-wrap gap-3">
                    <a href="mailto:{{ $supplier->pharmaUser->email }}" class="text-decoration-none d-flex align-items-center gap-2 text-dark text-secondary">
                        <i class="ph ph-envelope h5 m-0"></i>
                        <span class="text-secondary text-decoration-underline fw-medium font-size-16">{{ $supplier->pharmaUser->email }}</span>
                    </a>
                    <a href="tel:{{ $supplier->pharmaUser->mobile }}" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
                        <i class="ph ph-phone h5 m-0"></i>
                        <span class="text-primary text-decoration-underline fw-medium font-size-16">{{ $supplier->pharmaUser->mobile }}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="row gy-2">
            <div class="col-md-3">
                <span>{{ __('pharma::messages.gender') }}:</span>
                <p class="mt-2 h6 mb-0">{{ $supplier->pharmaUser->gender ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <span>{{ __('pharma::messages.dob') }}:</span>
                <p class="mt-2 h6 mb-0">{{ $supplier->pharmaUser->date_of_birth ?? '-' }}</p>
            </div>
        </div>

        @if($supplier->pharmaUser->clinic)
        <div class="mt-4 pt-4 border-top">
            <p class="mb-3">{{ __('pharma::messages.clinic_details') }}</p>
            <div>
                <h6 class="mb-1">{{ $supplier->pharmaUser->clinic->name ?? '-' }}</h6>
            </div>
        </div>
        @endif

    </div>
</div>
@endif