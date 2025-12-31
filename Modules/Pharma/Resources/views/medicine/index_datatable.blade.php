@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @php
                    $permissionsToCheck = ['edit_medicine', 'delete_medicine'];
                @endphp

                @if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission)) ||
                        auth()->user()->hasRole(['admin', 'demo_admin']))
                    <x-backend.quick-action url="{{ route('backend.medicine.bulk_action') }}">
                        <div class="">
                            <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                                style="width:100%">
                                <option value="">{{ __('messages.no_action') }}</option>
                                @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('edit_medicine'))
                                    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('delete_medicine'))
                                        <option value="delete">{{ __('messages.delete') }}</option>
                                    @endif
                                @endif
                            </select>
                        </div>


                    </x-backend.quick-action>
                @endif
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
                @if (auth()->user()->hasAnyRole(['admin', 'demo_admin']) ||
                        (auth()->user()->hasRole('pharma') && auth()->user()->can('add_medicine')))
                    <a href="{{ route('backend.medicine.create') }}" class="btn btn-primary ms-3">
                        <i class="ph ph-plus-circle"></i> {{ __('messages.new') }}
                    </a>
                @endif
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
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'medicine']) }}" data-ajax--cache="true">
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

    <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="add-stock-form">
                @csrf
                <input type="hidden" name="medicine_id" id="add-medicine-id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStockModalLabel">{{ __('pharma::messages.add_stock') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="batch_no" class="form-label">Batch No (Series No.)</label>
                            <input type="text" class="form-control" name="batch_no" id="batch_no"
                                placeholder="Enter batch number">
                        </div>

                        <div class="mb-3">
                            <label for="start_serial_no" class="form-label">Start Serial No</label>
                            <input type="number" class="form-control" name="start_serial_no" id="start_serial_no"
                                placeholder="e.g. 1001">
                        </div>

                        <div class="mb-3">
                            <label for="end_serial_no" class="form-label">End Serial No</label>
                            <input type="number" class="form-control" name="end_serial_no" id="end_serial_no"
                                placeholder="e.g. 1010">
                        </div>

                        <div class="mb-3">
                            <label for="add-quantity" class="form-label">{{ __('pharma::messages.add_stock') }}<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" id="add-quantity"
                                placeholder="{{ __('pharma::messages.add_stock') }}">
                            <div class="text-danger" id="quantity-error" style="display:none;">
                                {{ __('The quantity field is required.') }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submit-stock-btn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            {{ __('pharma::messages.add_stock') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="offcanvas offcanvas-end offcanvas-w-20" tabindex="-1" id="orderMedicineOffcanvas"
        aria-labelledby="orderMedicineLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="orderMedicineLabel">{{ __('pharma::messages.add_order') }}</h5>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body" id="orderMedicineContent">
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
        const canDeleteMedicine = @json(auth()->user()->can('delete_medicine'));


        const columns = [];
        if (canDeleteMedicine) {
            columns.push({
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                width: '0%',
                exportable: false,
                orderable: false,
                searchable: false,
            });
        }

        columns.push({
                data: 'name',
                name: 'name',
                title: "{{ __('pharma::messages.medicine_name') }}"
            }, {
                data: 'form.name',
                name: 'form.name',
                title: "{{ __('pharma::messages.form') }}"
            }, {
                data: 'dosage',
                name: 'dosage',
                title: "{{ __('pharma::messages.dosage') }}"
            }, {
                data: 'expiry_date',
                name: 'expiry_date',
                title: "{{ __('pharma::messages.expiry_date') }}"
            }, {
                data: 'supplier.name',
                name: 'supplier.name',
                title: "{{ __('pharma::messages.supplier') }}"
            }, {
                data: 'manufacturer.name',
                name: 'manufacturer.name',
                title: "{{ __('pharma::messages.manufacturer') }}"
            },
            @if (auth()->user()->user_type === 'admin')
                {
                    data: 'pharma_id',
                    name: 'pharma_id',
                    title: "{{ __('pharma::messages.pharma') }}"
                },
            @endif {
                data: 'selling_price',
                name: 'selling_price',
                title: "{{ __('pharma::messages.selling_price') }}"
            }, {
                data: 'quntity',
                name: 'quntity',
                title: "{{ __('pharma::messages.stock') }}"
            });


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

            if (window.jQuery && $.fn.select2) {
                $('#quick-action-type').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.no_action') }}"
                });
            }

            initDatatable({
                url: '{{ route('backend.medicine.index_data') }}',
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
                orderColumn: [],
            });
            $('#reset-filter').on('click', function(e) {

                $('#medicine').val(null).trigger('change');
                $('#dosage').val(null).trigger('change');
                $('#form').val(null).trigger('change');
                $('#category').val(null).trigger('change');
                $('#supplier').val(null).trigger('change');
                $('#manufacturer').val(null).trigger('change');
                $('#batch_no').val(null).trigger('change');
                window.renderedDataTable.ajax.reload(null, false);
            });

            $(document).on('click', '.view-supplier-btn', function() {
                let supplierId = $(this).data('id');
                $('#supplierDetailsContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#supplierDetailsOffcanvas').offcanvas('show');

                $.ajax({
                    url: `/app/suppliers/${supplierId}`, // adjust your route
                    type: 'GET',
                    success: function(response) {
                        $('#supplierDetailsContent').html(response
                            .html); // return HTML partial from controller
                    },
                    error: function() {
                        $('#supplierDetailsContent').html(
                            '<p class="text-danger">Failed to load supplier details.</p>');
                    }
                });
            });
        });

        function resetQuickAction() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        }

        $('#quick-action-type').change(function() {
            resetQuickAction()
        });


        $(document).on('click', '.open-add-stock', function() {
            $('#add-stock-form')[0].reset();
            $('#add-stock-form .is-invalid').removeClass('is-invalid');
            $('#add-stock-form .invalid-feedback').remove();
            $('#quantity-error').hide();

            var medicineId = $(this).data('id');
            $('#add-medicine-id').val(medicineId);
            $('#addStockModal').modal('show');
        });

        $('#add-stock-form').on('submit', function(e) {
            e.preventDefault();
            let $btn = $('#submit-stock-btn');
            let $spinner = $btn.find('.spinner-border');

            $btn.attr('disabled', true);
            $spinner.removeClass('d-none');
            $.ajax({
                url: '{{ route('backend.medicine.add_stock') }}', // create this route
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addStockModal').modal('hide');
                    window.successSnackbar(
                        "{{ __(key: 'pharma::messages.stock_added_successfully') }}");
                    if (window.renderedDataTable) {
                        window.renderedDataTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Laravel validation error
                        let errors = xhr.responseJSON.errors;

                        // Clear previous errors
                        $('#add-stock-form .is-invalid').removeClass('is-invalid');
                        $('#add-stock-form .invalid-feedback').remove();
                        $('#quantity-error').hide(); // Hide error div

                        // Show new errors
                        $.each(errors, function(field, messages) {
                            let input = $('#add-stock-form [name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + messages[0] +
                                '</div>');
                        });
                    } else {
                        toastr.error("{{ __('pharma::messages.something_went_wrong') }}");
                    }
                },
                complete: function() {
                    $btn.attr('disabled', false);
                    $spinner.addClass('d-none');
                }
            });
        });

        $(document).on('click', '.order-medicine-btn', function() {
            let medicineId = $(this).data('id');
            $('#orderMedicineContent').html(
                '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );
            $('#orderMedicineOffcanvas').offcanvas('show');
            let url =
                "{{ route('backend.order-medicine.create.with-medicine', ['medicineId' => '__medicineId__']) }}"
                .replace('__medicineId__', medicineId);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    $('#orderMedicineContent').html(response.html);

                    // Initialize Select2 for order_status and payment_status dropdowns
                    setTimeout(function() {
                        if ($('#order_status').length && !$('#order_status').hasClass(
                                'select2-hidden-accessible')) {
                            $('#order_status').select2({
                                width: '100%',
                                dropdownParent: $('#orderMedicineOffcanvas'),
                                minimumResultsForSearch: Infinity
                            });
                        }

                        if ($('#payment_status').length && !$('#payment_status').hasClass(
                                'select2-hidden-accessible')) {
                            $('#payment_status').select2({
                                width: '100%',
                                dropdownParent: $('#orderMedicineOffcanvas'),
                                minimumResultsForSearch: Infinity
                            });
                        }
                    }, 100);

                    // initialize flatpickr
                    flatpickr('.delivery-date-picker', {
                        dateFormat: 'Y-m-d',
                        minDate: 'today'
                    });
                },
                error: function() {
                    $('#orderMedicineContent').html(
                        '<p class="text-danger">Failed to load supplier details.</p>');
                }
            });
        });

        function calculateQuantityFromSerials() {
            const start = parseInt($('#start_serial_no').val());
            const end = parseInt($('#end_serial_no').val());

            if (!isNaN(start) && !isNaN(end) && end >= start) {
                const quantity = end - start + 1;
                $('#add-quantity').val(quantity);
            }
        }

        $(document).on('input', '#start_serial_no, #end_serial_no', function() {
            calculateQuantityFromSerials();
        });


        window.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                window.successSnackbar("{{ session('success') }}");
            @endif
            @if (session('error'))
                window.errorSnackbar("{{ session('error') }}");
            @endif
        });
    </script>
@endpush
