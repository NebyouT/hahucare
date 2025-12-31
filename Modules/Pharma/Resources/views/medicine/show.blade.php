@extends('pharma::layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    {{-- Medicine Details --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                            <h5 class="mb-0 fw-bold text-primary">{{ __('pharma::messages.medicine_details') }}</h5>
                            <span class="fw-semibold bg-primary-subtle px-3 py-2 rounded-pill">
                                {{ __('pharma::messages.category') }} : {{ optional($medicine->category)->name }}
                            </span>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center bg-body p-3 rounded-3 h-100 gap-3">
                                    <div class="bg-primary-subtle d-flex flex-column align-items-center justify-content-center p-3 rounded-circle">
                                        <i class="ph ph-pill text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p>{{ __('pharma::messages.medicine_name') }}</p>
                                        <h6 class="mb-0 fw-semibold fs-6">{{ $medicine->name }}</h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center bg-body p-3 rounded-3 h-100 gap-3">
                                    <div class="bg-primary-subtle d-flex flex-column align-items-center justify-content-center p-3 rounded-circle">
                                        <i class="ph ph-drop-half-bottom text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p>{{ __('pharma::messages.dosage') }}</p>
                                        <h6 class="mb-0 fw-semibold fs-6">{{ $medicine->dosage }}</h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center bg-body p-3 rounded-3 h-100 gap-3">
                                    <div class="bg-primary-subtle d-flex flex-column align-items-center justify-content-center p-3 rounded-circle">
                                        <i class="ph ph-package text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p>{{ __('pharma::messages.form') }}</p>
                                        <h6 class="mb-0 fw-semibold fs-6">{{ $medicine->form->name }}</h6>
                                    </div>
                                </div>
                            </div>  

                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center bg-body p-3 rounded-3 h-100 gap-3">
                                    <div class="bg-primary-subtle d-flex flex-column align-items-center justify-content-center p-3 rounded-circle">
                                        <i class="ph ph-calendar-x text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p>{{ __('pharma::messages.expiry_date') }}</p>
                                        <h6 class="mb-0 fw-semibold fs-6">
                                            {{ \Carbon\Carbon::parse($medicine->expiry_date)->format($dateformate) ?? '-' }}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex align-items-center bg-body p-3 rounded-3 h-100 gap-3">
                                    <div class="bg-primary-subtle d-flex flex-column align-items-center justify-content-center p-3 rounded-circle">
                                        <i class="ph ph-tag text-primary fs-4"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <p class="m-0">{{ __('pharma::messages.total_price') }}</p>
                                            <i class="ph ph-info text-heading fs-5" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="{{ __('pharma::messages.total_price_tooltip') }}">
                                            </i>
                                        </div>
                                        <h6 class="mb-0 fw-semibold fs-6">{{ Currency::format($medicine->purchase_price * $medicine->quntity) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Supplier List --}}
                <h5 class="mb-3">{{ __('pharma::messages.supplier_list') }}</h5>
                <div class="table-responsive">
                     <table class="table custom-table-border align-middle">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>{{ __('pharma::messages.date') }}</th>
                                <th>{{ __('pharma::messages.supplier_name') }}</th>
                                <th>{{ __('pharma::messages.quantity') }}</th>
                                <th>{{ __('pharma::messages.purchase_price') }}</th>
                                <th>{{ __('pharma::messages.selling_price') }}</th>
                                <th>{{ __('pharma::messages.manufacturer_name') }}</th>
                                <th>{{ __('pharma::messages.payment_terms') }}</th>
                                <th>{{ __('pharma::messages.inclusive_tax') }}</th>
                                <th>{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                       @if($suppliers)
                                <tr>
                                    <td>{{ $suppliers->created_at ? \Carbon\Carbon::parse($suppliers->created_at)->format($dateformate) : '' }}</td>
                                    <td>{{ optional($suppliers)->full_name  ?? __('pharma::messages.not_available') }}</td>
                                    <td>{{ optional($medicine)->quntity ?? __('pharma::messages.not_available') }}</td>
                                    <td>{{ \Currency::format(optional($medicine)->purchase_price ?? 0) ?? __('pharma::messages.not_available') }}</td>
                                    <td>{{ \Currency::format(optional($medicine)->selling_price ?? 0) ?? __('pharma::messages.not_available') }}</td>
                                    <td>{{ optional($medicine->manufacturer)->name ?? __('pharma::messages.not_available') }}</td>
                                    <td>{{ optional($suppliers)->payment_terms . ' ' . __('messages.days') ?? __('pharma::messages.not_available') }}</td>
                                    <td>
                                        @if(optional($medicine)->is_inclusive_tax == 1)
                                            <span class="badge bg-success">{{ __('pharma::messages.enable') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('pharma::messages.disable') }}</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('backend.medicine.history', $medicine->id) }}"><i class="ph ph-eye"></i></a></td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('pharma::messages.no_suppliers_found') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table> 
                    <table id="datatable" class="table table-responsive">
                    </table>
                </div>
            </div>
        </div>
    </div>

<div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="medicineDetailsOffcanvas" aria-labelledby="medicineDetailsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="medicineDetailsLabel">{{ __('pharma::messages.supplier_details') }}</h5>
        <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i class="ph ph-x-circle"></i></button>
    </div>
    <div class="offcanvas-body" id="medicineDetailsContent">
        {{-- Content loaded via AJAX --}}
        <div class="text-center my-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>
</div>
    
@endsection
@push ('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush
@push ('after-scripts')
<script src="{{ asset('modules/pharma/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script type="text/javascript" defer>
     const columns = [
        {
            data: 'created_at',
            name: 'created_at',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.date') }}"
        },
        {
            data: 'supplier.name',
            name: 'supplier.name',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.supplier') }}"
        },
        {
            data: 'dosage',
            name: 'dosage',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.dosage') }}"
        },
        {
            data: 'quntity',
            name: 'quntity',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.quantity') }}"
        },
        {
            data: 'purchase_price',
            name: 'purchase_price',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.purchase_price') }}"
        },
        
        {
            data: 'selling_price',
            name: 'selling_price',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.selling_price') }}"
        },
        {
            data: 'manufacturer_name',
            name: 'manufacturer_name',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.manufacturer_name') }}"
        },
        {
            data: 'tax',
            name: 'tax',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.inclusive_tax') }}"
        },
        {
            data: 'payment_terms',
            name: 'payment_terms',
            orderable: false,
            searchable: false,
            title: "{{ __('pharma::messages.payment_terms') }}"
        },
    ];

    const actionColumn = [{
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false,
        title: "{{ __('service.lbl_action') }}",
        width: '5%'
    }];

    let finalColumns = [
        ...columns,
        ...actionColumn
    ];

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.medicine.medicine-detail-table") }}',
            finalColumns,
            advanceFilter: () => {
                return {
                    medicine_id: '{{ $medicine->id }}'
                };
            },
            orderColumn: [
                [1, "asc"]
            ],
        });


        $(document).on('click', '.view-medicine-btn', function () {
            let url = $(this).data('url');
            $('#medicineDetailsContent').html('<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>');
            $('#medicineDetailsOffcanvas').offcanvas('show');

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#medicineDetailsContent').html(response.html);                
                },
                error: function () {
                    $('#medicineDetailsContent').html('<p class="text-danger">Failed to load supplier details.</p>');
                }
            });
        });

        
    });
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))

</script>
@endpush

