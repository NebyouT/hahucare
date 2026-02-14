<div class="card">
    <div class="card-body">
        <div class="d-flex gap-3 align-items-start">
            <img src="{{ $supplier->file_url ?? asset('img/default.webp') }}" class="img-fluid avatar avatar-80 avatar-rounded" alt="Supplier Image">
            <div>
                <h4 class="mb-3">{{ $supplier->first_name }} {{ $supplier->last_name }}</h4>
                <div class="d-flex align-items-center gap-4 mb-2">
                    <a href="mailto:{{ $supplier->email }}" class="text-decoration-none d-flex align-items-center gap-2 text-dark text-secondary">
                        <i class="ph ph-envelope"></i>
                        {{ $supplier->email }}
                    </a>
                    <a href="tel:{{ $supplier->contact_number }}" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
                        <i class="ph ph-phone"></i>
                        {{ $supplier->contact_number }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-6">
                <div class="mb-3">
                    <label class="">{{ __('pharma::messages.supplier_type') }}</label>
                    <div class="text-dark">{{ $supplier->supplierType->name ?? '-' }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label class="">{{ __('pharma::messages.payment_terms') }}</label>
                    <div class="text-dark">{{ $supplier->payment_terms }} {{ __('pharma::messages.days') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
