<div class="order-details">
    <!-- Order Info -->
    <div class="order-info mb-4">
        <p class="mb-3">{{ $orderDetail->created_at->format($dateformate) }}</p>
        @php
            $statusLabels = config('constant.PAYMENT_STATUS');
            $paymentStatus = array_flip($statusLabels)[$orderDetail->payment_status] ?? 'Unknown';
            $orderStatusLabels = config('constant.ORDER_STATUS');
            $orderStatus = array_flip($orderStatusLabels)[$orderDetail->order_status] ?? 'Unknown';
        @endphp
        <div class="mb-3">
            <span class="badge bg-{{ $orderDetail->payment_status == 'completed' ? 'success' : 'warning' }} me-2">
                {{ __('pharma::messages.payment') }}: {{ ucfirst($orderDetail->payment_status ?? '-') }}
            </span>
            <span class="badge bg-{{ $orderDetail->order_status == 'delivered' ? 'success' : 'secondary' }}">
                {{ __('pharma::messages.order') }}: {{ ucfirst($orderDetail->order_status ?? '-') }}
            </span>
        </div>
    </div>

    <!-- Medicine Details -->
    <div class="section mb-4">
        <h6>{{ __('pharma::messages.medicine_information') }}</h6>
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.name') }}:</strong>
            {{ $orderDetail->medicine?->name ?? __('pharma::messages.not_available') }}
        </div>
        @if ($orderDetail->medicine?->category)
            <div class="info-item">
                <strong class="text-dark">{{ __('pharma::messages.category') }}:</strong>
                {{ $orderDetail->medicine?->category?->name ?? __('pharma::messages.not_available') }}
            </div>
        @endif
        @if ($orderDetail->medicine?->dosage)
            <div class="info-item">
                <strong class="text-dark">{{ __('pharma::messages.lbl_dosage') }}:</strong>
                {{ $orderDetail->medicine?->dosage ?? __('pharma::messages.not_available') }}
            </div>
        @endif
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.quantity') }}:</strong>
            {{ number_format($orderDetail->quantity) }}
        </div>
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.purchase_price') }}:</strong>
            {{ $orderDetail->medicine?->purchase_price ? Currency::format($orderDetail->medicine?->purchase_price ?? 0) : __('pharma::messages.not_available') }}
        </div>
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.total_amount') }}:</strong>
            {{ $orderDetail->total_amount ? Currency::format($orderDetail->total_amount ?? 0) : __('pharma::messages.not_available') }}
        </div>
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.delivery_date') }}:</strong>
            {{ $orderDetail->delivery_date
                ? \Carbon\Carbon::parse($orderDetail->delivery_date)->format($dateformate)
                : __('pharma::messages.not_available') }}
        </div>
    </div>

    <!-- Supplier Details -->
    <div class="section mb-4">
        <h6>{{ __('pharma::messages.supplier_information') }}</h6>
        <div class="supplier-info">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ $orderDetail->medicine?->supplier?->profile_image ?? default_user_avatar() }}"
                    width="40" height="40" class="rounded-circle me-3"
                    alt="{{ __('pharma::messages.supplier') }}"
                    onerror="this.src='{{ asset('public/img/default-avatar.png') }}'">
                <div>
                    <div class="fw-bold">
                        {{ $orderDetail->medicine?->supplier?->full_name ?? __('pharma::messages.not_available') }}
                    </div>
                    <div class="font-size-14">
                        {{ $orderDetail->medicine?->supplier?->email ?? __('pharma::messages.not_available') }}
                    </div>
                </div>
            </div>
            @if ($orderDetail->medicine?->supplier?->phone)
                <div class="info-item">
                    <strong class="text-dark">{{ __('pharma::messages.phone') }}:</strong>
                    {{ $orderDetail->medicine?->supplier?->phone ?? __('pharma::messages.not_available') }}
                </div>
            @endif
        </div>

    </div>
    <!-- Manufacturer Details -->
    <div class="section mb-4">
        <h6>{{ __('pharma::messages.manufacturer_information') }}</h6>
        <div class="info-item">
            <strong class="text-dark">{{ __('pharma::messages.name') }}:</strong>
            {{ $orderDetail->medicine?->manufacturer?->name ?? __('pharma::messages.not_available') }}
        </div>
        @if ($orderDetail->medicine?->manufacturer?->address)
            <div class="info-item">
                <strong>{{ __('pharma::messages.address') }}:</strong>
                {{ $orderDetail->medicine?->manufacturer?->address }}
            </div>
        @endif
    </div>

    <!-- Close Button -->
    <div class="text-center mt-4">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="offcanvas">
            {{ __('pharma::messages.close') }}
        </button>
    </div>
</div>
