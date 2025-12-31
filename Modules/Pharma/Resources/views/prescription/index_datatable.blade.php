@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @php
                    $permissionsToCheck = ['edit_prescription', 'delete_prescription'];
                @endphp

                @if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission)) ||
                        auth()->user()->hasRole('admin') ||
                        auth()->user()->hasRole('demo_admin'))
                    <x-backend.quick-action url="{{ route('backend.prescription.encounter_bulk_action') }}">
                        <div class="">
                            <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                                style="width:100%">
                                <option value="">{{ __('messages.no_action') }}</option>
                                @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('edit_prescription'))
                                    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('delete_prescription'))
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
                <div>
                    <div class="datatable-filter border rounded">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select"
                            style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="0" {{ $filter['column_status'] == '0' ? 'selected' : '' }}>
                                {{ __('messages.unpaid') }}</option>
                            <option value="1" {{ $filter['column_status'] == '1' ? 'selected' : '' }}>
                                {{ __('messages.paid') }}</option>
                        </select>
                    </div>
                </div>

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
            <label class="form-label" for="patient"> {{ __('pharma::messages.patient') }}</label>
            <select id="patient" name="patient" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('messages.select_patient') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'customers']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="doctor"> {{ __('pharma::messages.doctor') }}</label>
            <select id="doctor" name="doctor" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('messages.select_doctor_name') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'doctors']) }}" data-ajax--cache="true">
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="status"> {{ __('pharma::messages.status') }}</label>
            <select id="status" name="status" class="form-control select2" data-filter="select"
                data-placeholder="{{ __('pharma::messages.all') }}">
                <option value="">{{ __('pharma::messages.all') }}</option>
                <option value="0">{{ __('pharma::messages.pending') }}</option>
                <option value="1">{{ __('pharma::messages.completed') }}</option>
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="payment_status"> {{ __('pharma::messages.payment_status') }}</label>
            <select id="payment_status" name="payment_status" class="form-control select2" data-filter="select"
                data-placeholder="{{ __('pharma::messages.all') }}">
                <option value="">{{ __('pharma::messages.all') }}</option>
                <option value="0">{{ __('messages.unpaid') }}</option>
                <option value="1">{{ __('messages.paid') }}</option>
            </select>
        </div>

        <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
    </x-backend.advance-filter>

    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="supplierDetailsOffcanvas"
        aria-labelledby="supplierDetailsLabel">
        <div class="offcanvas-header mb-5 border-bottom-gray-700">
            <h5 class="mb-0" id="supplierDetailsLabel">{{ __('pharma::messages.supplier_details') }}</h5>

            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body pt-0" id="supplierDetailsContent">

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
        const successMessage = @json(__('pharma::messages.status_update_message'));
        const canDeletePrescription = @json(auth()->user()->can('delete_prescription'));
        const columns = [];
        if (canDeletePrescription) {
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
            data: 'encounter_id',
            name: 'encounter_id',
            title: "{{ __('pharma::messages.encounter_id') }}"
        }, {
            data: 'created_at',
            name: 'created_at',
            title: "{{ __('pharma::messages.date_time') }}"
        }, {
            data: 'user.encounter.user',
            name: 'user.encounter.user',
            title: "{{ __('pharma::messages.patient') }}"
        }, {
            data: 'user.encounter.doctor',
            name: 'user.encounter.doctor',
            title: "{{ __('pharma::messages.doctor') }}"
        }, {
            data: 'clinic_name',
            name: 'clinic_name',
            title: "Clinic Name"
        }, {
            data: 'pharma',
            name: 'pharma',
            title: "Pharma"
        }, {
            data: 'prescriptions.medicine.selling_price',
            name: 'prescriptions.medicine.selling_price',
            title: "{{ __('pharma::messages.medicine_price') }}"
        }, {
            data: 'prescription_status',
            name: 'prescription_status',
            title: "{{ __('pharma::messages.status') }}",
            orderable: false,
            searchable: false,
        }, {
            data: 'payment_status',
            name: 'billingrecord.payment_status',
            title: "{{ __('pharma::messages.payment_status') }}",
            orderable: false,
            searchable: false,
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
            // Initialize Select2 for quick action and status filter dropdowns
            if (window.jQuery && $.fn.select2) {
                $('#quick-action-type').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.no_action') }}"
                });

                $('#column_status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.all') }}"
                });
            }

            // Initialize Select2 for advance filter dropdowns when offcanvas opens
            // These dropdowns don't have AJAX URLs, so they need explicit initialization
            $('#offcanvasExample').on('shown.bs.offcanvas', function() {
                setTimeout(function() {
                    // Initialize status dropdown (non-AJAX)
                    var $statusSelect = $('#offcanvasExample #status');
                    if ($statusSelect.length && !$statusSelect.hasClass(
                            'select2-hidden-accessible')) {
                        $statusSelect.select2({
                            width: '100%',
                            dropdownParent: $('#offcanvasExample'),
                            minimumResultsForSearch: Infinity,
                            placeholder: "{{ __('pharma::messages.all') }}"
                        });
                    }

                    // Initialize payment_status dropdown (non-AJAX)
                    var $paymentStatusSelect = $('#offcanvasExample #payment_status');
                    if ($paymentStatusSelect.length && !$paymentStatusSelect.hasClass(
                            'select2-hidden-accessible')) {
                        $paymentStatusSelect.select2({
                            width: '100%',
                            dropdownParent: $('#offcanvasExample'),
                            minimumResultsForSearch: Infinity,
                            placeholder: "{{ __('pharma::messages.all') }}"
                        });
                    }
                }, 150); // Delay to ensure advance-filter component initialization completes first
            });

            $('#pharma_prescription_user').val(getFilterParam('pharma_prescription_user'));
            $('#special_match').val(getFilterParam('special_match'));
            initDatatable({
                url: '{{ route('backend.prescription.index_data') }}',
                finalColumns,
                advanceFilter: () => {
                    const filterData = {
                        patient: $('#patient').val(),
                        doctor: $('#doctor').val(),
                        status: $('#status').val(),
                        payment_status: $('#payment_status').val(),
                        column_status: $('#column_status').val(),
                        pharma_prescription_user: getFilterParam('pharma_prescription_user'),
                        special_match: getFilterParam('special_match'),
                    };
                    return filterData;
                },
                orderColumn: [
                    [1, "desc"]
                ],
                drawCallback: () => {
                    // Initialize Select2 for dynamically created prescription_status and payment_status dropdowns
                    $('.prescription_status').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                width: '100%',
                                minimumResultsForSearch: Infinity
                            });
                        }
                    });

                    $('.payment_status').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                width: '100%',
                                minimumResultsForSearch: Infinity
                            });
                        }
                    });
                }
            });
            $('#reset-filter').on('click', function(e) {
                // Reset Select2 dropdowns
                $('#patient, #doctor, #status, #payment_status').val(null).trigger('change');
                $('#column_status').val(null).trigger('change');
                window.renderedDataTable.ajax.reload(null, false);
            });


            $(document).on('change', '.prescription_status', function() {
                const selectedStatus = $(this).val();
                const orderId = $(this).data('encounter-id');

                $.ajax({
                    url: '{{ route('backend.prescription.update_prescription_status') }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: selectedStatus,
                        encounter_id: orderId
                    },
                    success: function(data) {
                        window.successSnackbar(successMessage);
                        window.renderedDataTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;

                        let messages = [];

                        if (Array.isArray(response)) {
                            response.forEach(function(med) {
                                messages.push(
                                    `⚠️ ${med.name} - Required: ${med.required_quantity}, Available: ${med.available_quantity}`
                                );
                            });
                        } else if (response.adjusted_medicines) {
                            response.adjusted_medicines.forEach(function(med) {
                                messages.push(
                                    `⚠️ ${med.name} - Required: ${med.required_quantity}, Available: ${med.available_quantity}`
                                );
                            });
                        }

                        if (messages.length > 0) {
                            window.errorSnackbar(messages.join('<br>'));
                        } else {
                            window.errorSnackbar(response.message ||
                                'An error occurred. Please try again.');
                        }
                    }


                });
            });

            $(document).on('change', '.payment_status', function() {
                const selectedStatus = $(this).val();
                const orderId = $(this).data('payment-status-id');



                $.ajax({
                    url: '{{ route('backend.prescription.update_prescription_payment_status') }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: selectedStatus,
                        encounter_id: orderId
                    },
                    success: function(data) {

                        window.successSnackbar(successMessage);
                        window.renderedDataTable.ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        window.errorSnackbar(xhr.responseJSON.message ||
                            'An error occurred. Please try again.');

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

        function getFilterParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(`filter[${param}]`);
        }

        @if (session('success'))
            window.addEventListener('DOMContentLoaded', () => {
                window.successSnackbar(@json(session('success')));
            });
        @endif

        @if (session('error'))
            window.addEventListener('DOMContentLoaded', () => {
                window.errorSnackbar(@json(session('error')));
            });
        @endif
    </script>
@endpush
