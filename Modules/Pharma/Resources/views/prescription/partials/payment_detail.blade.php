<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between py-2">
            <div>{{ __('pharma::messages.medicine_total') }}</div>
            <div class="fw-semibold">{{ \Currency::format($totalMedicinePrice) }}</div>
        </div>

        @if (!empty($exclusiveTaxes))
            <div class="d-flex justify-content-between py-2 mt-3">
                <strong>{{ __('pharma::messages.tax') }}</strong>
            </div>
            @foreach ($exclusiveTaxes as $tax)
                @php
                    // Ensure tax is an array and extract values safely
                    $taxArray = is_array($tax) ? $tax : [];
                    $taxType = $taxArray['type'] ?? 'percent';
                    $taxValue = is_numeric($taxArray['value'] ?? null) ? $taxArray['value'] : 0;
                    $taxTitle = is_string($taxArray['title'] ?? null) ? $taxArray['title'] : __('pharma::messages.exclusive_tax');
                    
                    $amount = $taxType === 'percent'
                        ? \Currency::format(($totalMedicinePrice * $taxValue / 100))
                        : \Currency::format($taxValue);
                @endphp
                <div class="d-flex justify-content-between py-1">
                    <div>
                        {{ $taxTitle }} 
                        ({{ $taxType === 'fixed' ? getCurrencySymbol() : '' }}{{ $taxValue }}{{ $taxType === 'percent' ? '%' : '' }})
                    </div>                    
                    <div class="fw-semibold">
                        {{ $amount }}
                    </div>
                </div>
            @endforeach

        @endif

        <div class="d-flex justify-content-between mt-3 pt-3 fs-5 border-top">
            <strong>{{ __('pharma::messages.grand_total') }}</strong>
            <strong class="text-danger">{{ $totalAmount }}</strong>
        </div>
    </div>
</div>
