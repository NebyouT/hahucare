@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <div>
                    <button type="button" class="btn btn-primary" data-modal="export">
                        <i class="ph ph-export me-1"></i> {{ __('messages.export') }}
                    </button>
                </div>
            </div>

            <x-slot name="toolbar">
                <div class="input-group flex-nowrap border rounded">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">
                </div>
                <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group ms-3" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i
                        class="ph ph-funnel"></i>{{ __('messages.advance_filter') }}</button>

            </x-slot>

        </x-backend.section-header>

        <table id="datatable" class="table table-responsive">
        </table>
    </div>
    <x-backend.advance-filter>
        <x-slot name="title">
            <h4>{{ __('service.lbl_advanced_filter') }}</h4>
        </x-slot>

        <div class="form-group datatable-filter">
            <label class="form-label" for="medicine"> {{ __('pharma::messages.medicine') }}</label>
            <select id="medicine" name="medicine" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_medicine_name') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'expired_medicine']) }}"
                data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="dosage"> {{ __('pharma::messages.dosage') }}</label>
            <select id="dosage" name="dosage" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_dosage') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'dosage']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="form"> {{ __('pharma::messages.form') }}</label>
            <select id="form" name="form" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_form') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'form']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="category"> {{ __('pharma::messages.category') }}</label>
            <select id="category" name="category" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.select_category') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'category']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="supplier"> {{ __('pharma::messages.supplier') }}</label>
            <select id="supplier" name="supplier" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_supplier') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="manufacturer"> {{ __('pharma::messages.manufacturer') }}</label>
            <select id="manufacturer" name="manufacturer" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_manufacturer') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'manufacturer']) }}"
                data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="batch_no"> {{ __('pharma::messages.batch_no') }}</label>
            <select id="batch_no" name="batch_no" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.placeholder_batch_no') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'batch_no']) }}" data-ajax--cache="true">
            </select>
        </div>


        <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
    </x-backend.advance-filter>

    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="expiredMedicineDetailsOffcanvas"
        aria-labelledby="medicineDetailsLabel">
        <div class="offcanvas-header">

            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body" id="expiredMedicineDetailsContent">

            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ asset('modules/pharma/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>
    <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        const columns = [{
                data: 'name',
                name: 'name',
                title: "{{ __('pharma::messages.medicine_name') }}"
            },
            {
                data: 'form.name',
                name: 'form.name',
                title: "{{ __('pharma::messages.form') }}"
            },
            {
                data: 'dosage',
                name: 'dosage',
                title: "{{ __('pharma::messages.dosage') }}"
            },
            {
                data: 'expiry_date',
                name: 'expiry_date',
                title: "{{ __('pharma::messages.expiry_date') }}"
            },
            {
                data: 'supplier.name',
                name: 'supplier.name',
                title: "{{ __('pharma::messages.supplier') }}"
            },

            {
                data: 'manufacturer.name',
                name: 'manufacturer.name',
                title: "{{ __('pharma::messages.manufacturer') }}"
            },
            {
                data: 'selling_price',
                name: 'selling_price',
                title: "{{ __('pharma::messages.selling_price') }}"
            },
            {
                data: 'quntity',
                name: 'quntity',
                title: "{{ __('pharma::messages.stock') }}"
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
                url: '{{ route('backend.expired-medicine.index_data') }}',
                finalColumns,
                advanceFilter: () => {
                    return {
                        name: $('#medicine').val(),
                        dosage: $('#dosage').val(),
                        form: $('#form').val(),
                        category: $('#category').val(),
                        supplier: $('#supplier').val(),
                        manufacturer: $('#manufacturer').val(),
                        batch_no: $('#batch_no').val(),
                    };
                },
                orderColumn: [
                    [1, "asc"]
                ],
            });
            $('#reset-filter').on('click', function(e) {
                $('#medicine').val('');
                $('#dosage').val('');
                $('#form').val('');
                $('#category').val('');
                $('#supplier').val('');
                $('#manufacturer').val('');
                $('#batch_no').val('');
                window.renderedDataTable.ajax.reload(null, false);
            });

            $(document).on('click', '.view-expired-medicine-btn', function() {
                let medicineId = $(this).data('id');
                let supplierId = $(this).attr('supplier-id');
                let url = $(this).data('url');
                $('#expiredMedicineDetailsContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#expiredMedicineDetailsOffcanvas').offcanvas('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {

                        $('#expiredMedicineDetailsContent').html(response.html);
                    },
                    error: function() {
                        $('#expiredMedicineDetailsContent').html(
                            '<p class="text-danger">{{ __('pharma::messages.failed_to_load_supplier_details') }}</p>'
                        );
                    }
                });
            });
        });
    </script>
@endpush
