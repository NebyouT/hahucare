<h5 class="mt-3 mb-3">{{ __('pharma::messages.supplier_details') }}</h5>
<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.name') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.contact_no') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.email') }}:</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">{{ $suppliers->first_name }} {{ $suppliers->last_name }}</div>
            <div class="col-md-4 fw-bold">{{ $suppliers->contact_number }}</div>
            <div class="col-md-4 fw-bold">{{ $suppliers->email }}</div>
        </div>
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.payment_terms') }}:</div>

        </div>

        <div class="row">
            <div class="col-md-4 fw-bold">{{ $suppliers->payment_terms }}</div>
        </div>
    </div>
</div>


<h5 class="mt-5 mb-3">{{ __('pharma::messages.inventory_details') }}:</h5>
<div class="card">
    <div class="card-body">
        <!-- Labels Row -->
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.quantity') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.expiry_date') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.purchase_price') }}:</div>
        </div>
        <!-- Values Row -->
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">{{ $medicine->quntity }}</div>
            <div class="col-md-4 fw-bold">{{ $medicine->expiry_date }}</div>
            <div class="col-md-4 fw-bold">{{ $medicine->purchase_price }}</div>
        </div>

        <!-- Second Label Row -->
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.selling_price') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.stock_value') }}:</div>
            <div class="col-md-3">{{ __('pharma::messages.tax') }}:</div>
        </div>
        <!-- Second Value Row -->
        <div class="row">
            <div class="col-md-4 fw-bold">{{ $medicine->selling_price }}</div>
            <div class="col-md-4 fw-bold">{{ $medicine->stock_value }}</div>
            <div class="col-md-3 fw-bold">{!! $taxesHtml ?? '' !!}</div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-3">{{ __('pharma::messages.other_details') }}</h5>
<div class="card mb-3">
    <div class="card-body">
        <!-- Labels Row -->
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.manufacturer') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.start_serial_no') }}:</div>
            <div class="col-md-4">{{ __('pharma::messages.end_serial_no') }}:</div>
        </div>
        <!-- Values Row -->
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">{{ $medicine->manufacturer->name }}</div>
            <div class="col-md-4 fw-bold">{{ $medicine->start_serial_no }}</div>
            <div class="col-md-4 fw-bold">{{ $medicine->end_serial_no }}</div>
        </div>

        <!-- Second Label Row -->
        <div class="row mb-1 text-body fw-semibold">
            <div class="col-md-4">{{ __('pharma::messages.batch_no') }}:</div>
        </div>
        <!-- Second Value Row -->
        <div class="row">
            <div class="col-md-4 fw-bold">{{ $medicine->batch_no }}</div>
        </div>
    </div>
</div>
