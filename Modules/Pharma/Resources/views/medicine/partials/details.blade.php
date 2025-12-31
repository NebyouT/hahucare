<div class="card mb-3">
    <div class="card-body">
        <!-- Labels Row -->

        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.name') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.contact_no') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.email') }}:</div>
        </div>
        <!-- Values Row -->
        <div class="row mb-3">
            <div class="col-md-4 fw-bold text-black">{{ $suppliers->first_name }} {{ $suppliers->last_name }}</div>
            <div class="col-md-4 fw-bold text-black">{{ $suppliers->contact_number }}</div>
            <div class="col-md-4 fw-bold text-black">{{ $suppliers->email }}</div>
        </div>

        <!-- Second Label Row -->
        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.payment_terms') }}:</div>
        </div>
        <!-- Second Value Row -->
        <div class="row">
            <div class="col-md-4 fw-bold text-black">{{ $suppliers->payment_terms . ' ' . __('pharma::messages.days') }}
            </div>

        </div>
    </div>
</div>


<h5 class="mt-5 mb-3">{{ __('pharma::messages.inventory_details') }}:</h5>
<div class="card">
    <div class="card-body">
        <!-- Labels Row -->
        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.quantity') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.expiry_date') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.purchase_price') }}:</div>
        </div>
        <!-- Values Row -->
        <div class="row mb-3">
            <div class="col-md-4 fw-bold text-black">{{ $medicine->quntity }}</div>
            <div class="col-md-4 fw-bold text-black">
                {{ \Carbon\Carbon::parse($medicine->expiry_date)->format($dateformate) }}</div>
            <div class="col-md-4 fw-bold text-black">{{ \Currency::format($medicine->purchase_price) }}</div>
        </div>

        <!-- Second Label Row -->
        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.selling_price') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.stock_value') }}:</div>
            <div class="col-md-3">{{ __('pharma::messages.exclusive_tax') }}:</div>
        </div>
        <!-- Second Value Row -->
        <div class="row">
            <div class="col-md-4 fw-bold text-black">{{ \Currency::format($medicine->selling_price) }}</div>
            <div class="col-md-4 fw-bold text-black">{{ $medicine->stock_value }}</div>
            <div class="col-md-3 fw-bold text-black">{!! $taxesHtml ?? '' !!}</div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-3">{{ __('pharma::messages.other_details') }}</h5>
<div class="card mb-3">
    <div class="card-body">
        <!-- Labels Row -->
        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.manufacturer') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.start_serial_no') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.end_serial_no') }}:</div>
        </div>
        <!-- Values Row -->
        <div class="row mb-3">

            <div class="col-md-4 fw-bold text-black">{{ $medicine->manufacturer->name }}</div>
            <div class="col-md-4 fw-bold text-black">{{ $medicine->start_serial_no }}</div>
            <div class="col-md-4 fw-bold text-black">{{ $medicine->end_serial_no }}</div>
        </div>

        <!-- Second Label Row -->
        <div class="row mb-1 fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.batch_no') }}:</div>

        </div>
        <!-- Second Value Row -->
        <div class="row">
            <div class="col-md-4 fw-bold text-black">{{ $medicine->batch_no }}</div>

        </div>
    </div>
</div>

<style>
    hr {
        border: none;
        border-top: 1px solid grey;
    }
</style>
