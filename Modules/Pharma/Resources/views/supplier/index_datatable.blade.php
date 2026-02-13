@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @php
                    $permissionsToCheck = ['edit_suppliers', 'delete_suppliers'];
                @endphp

                @if (collect($permissionsToCheck)->contains(fn($permission) => auth()->user()->can($permission)) ||
                        auth()->user()->hasRole(['admin','demo_admin','vendor']))
                    <x-backend.quick-action url="{{ route('backend.suppliers.bulk_action') }}">
                        <div class="">
                            <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                                <option value="">{{ __('messages.no_action') }}</option>
                            
                                @if (auth()->user()->hasRole(['admin','demo_admin','vendor']) || auth()->user()->can('edit_suppliers'))
                                    <option value="change-status">{{ __('messages.status') }}</option>
                                @endif

                                @if (auth()->user()->hasRole(['admin','demo_admin','vendor']) || auth()->user()->can('delete_suppliers'))
                                    <option value="delete">{{ __('messages.delete') }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="select-status d-none quick-action-field" id="change-status-action">
                            <select name="status" class="form-control select2" id="status" style="width:100%">
                                <option value="" selected>{{ __('messages.select_status') }}</option>
                                <option value="1">{{ __('messages.active') }}</option>
                                <option value="0">{{ __('messages.inactive') }}</option>
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
                            <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                                {{ __('messages.inactive') }}</option>
                            <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                                {{ __('messages.active') }}</option>
                        </select>
                    </div>
                </div>

                <div class="input-group flex-nowrap border rounded">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">
                </div>
                <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group"
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i
                            class="ph ph-funnel"></i>{{ __('messages.advance_filter') }}</button>
                    @if (auth()->user()->hasAnyRole(['admin', 'demo_admin','vendor']) ||
                            (auth()->user()->hasRole('pharma') && auth()->user()->can('add_suppliers')))
                        <a href="{{ route('backend.suppliers.create') }}" class="btn btn-primary">
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


        {{-- Supplier Type --}}
        <div class="form-group datatable-filter">
            <label class="form-label" for="supplier_type"> {{ __('pharma::messages.supplier_type') }}</label>
            <select id="supplier_type" name="supplier_type" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.select_supplier_type') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier_type']) }}"
                data-ajax--cache="true">
            </select>
        </div>

        {{-- Contact Number --}}
        <div class="form-group datatable-filter">
            <label class="form-label" for="contact_number"> {{ __('pharma::messages.contact_number') }}</label>
            <select id="contact_number" name="contact_number" data-filter="select" class="select2 form-control"
                data-placeholder="{{ __('pharma::messages.select_contact_number') }}"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier_contact_number']) }}"
                data-ajax--cache="true">
            </select>
        </div>

        @if (auth()->user()->hasRole(['admin', 'demo_admin']))
            <div class="form-group datatable-filter">
                <label class="form-label" for="pharma_id"> {{ __('pharma::messages.pharma') }}</label>
                <select id="pharma_id" name="pharma_id" data-filter="select" class="select2 form-control"
                    data-placeholder="{{ __('pharma::messages.select_pharma') }}"
                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_id']) }}"
                    data-ajax--cache="true">
                </select>
            </div>
        @endif

        <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>

    </x-backend.advance-filter>

    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="supplierDetailsOffcanvas"
        aria-labelledby="supplierDetailsLabel">
        <div class="offcanvas-header mb-5 border-bottom-gray-700">
            <h5 class="mb-0" id="supplierDetailsLabel">{{ __('pharma::messages.supplier_details') }}</h5>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body pt-0" id="supplierDetailsContent">
            {{-- Content loaded via AJAX --}}
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
        const canEditSuppliers = @json(auth()->user()->can('edit_suppliers'));

        const columns = [];

        if (canEditSuppliers) {
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
            data: 'supplier_name',
            name: 'first_name',
            title: "{{ __('pharma::messages.supplier_name') }}",
             orderable: true
        }, {
            data: 'contact_number',
            name: 'contact_number',
            title: "{{ __('pharma::messages.contact_number') }}",
             orderable: true
        }, {
            data: 'supplierType.name',
            name: 'supplierType.name',
            title: "{{ __('pharma::messages.supplier_type') }}",
             orderable: true
        },
        {
            data: 'pharma_id',
            name: 'pharma_id',
            title: "{{ __('pharma::messages.pharma') }}",
             orderable: true
        }, {
            data: 'payment_terms',
            name: 'payment_terms',
            title: "{{ __('pharma::messages.payment_terms') }}",
             orderable: true
        }, {
            data: 'status',
            name: 'status',
            title: "{{ __('pharma::messages.status') }}",
            orderable: false,
            searchable: true,
            width: '5%'
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

                $('#status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.select_status') }}"
                });

                $('#column_status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.all') }}"
                });
            }

            initDatatable({
                url: '{{ route('backend.suppliers.index_data') }}',
                finalColumns,
                advanceFilter: () => {
                    return {
                        supplier_name: $('#supplier_name').val(),
                        supplier_type: $('#supplier_type').val(),
                        contact_number: $('#contact_number').val(),
                        pharma_id: $('#pharma_id').val(),
                        column_status: $('#column_status').val(),
                    };
                },
                orderColumn: [
                    [1, "asc"]
                ],
            });
            $('#reset-filter').on('click', function(e) {
                $('#supplier_name, #supplier_type, #contact_number, #pharma_id').val(null).trigger('change');
                window.renderedDataTable.ajax.reload(null, false);
            });
            var offcanvasElem = document.getElementById('supplierDetailsOffcanvas');
            console.log('dir', document.documentElement.dir);

            if (document.documentElement.dir === 'rtl') {
                offcanvasElem.classList.add('offcanvas-start');
                offcanvasElem.classList.remove('offcanvas-end');
            } else {
                offcanvasElem.classList.add('offcanvas-end');
            }
            $(document).on('click', '.view-supplier-btn', function() {
                let supplierId = $(this).data('id');
                $('#supplierDetailsContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#supplierDetailsOffcanvas').offcanvas('show');

                $.ajax({
                    url: `{{ route('backend.suppliers.show', ['supplier' => ':id']) }}`.replace(':id', supplierId),
                    type: 'GET',
                    success: function(response) {
                        $('#supplierDetailsContent').html(response
                            .html);
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
