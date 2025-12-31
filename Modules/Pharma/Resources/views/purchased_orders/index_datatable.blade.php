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
                    <x-backend.quick-action url="{{ route('backend.order-medicine.bulk_action') }}">
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
                <div>
                    <div class="datatable-filter border rounded" style="width: auto; min-width: 120px;">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select"
                            style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="pending" {{ $filter['payment_status'] == 'pending' ? 'selected' : '' }}>
                                {{ __('pharma::messages.pending') }}</option>
                            <option value="delivered" {{ $filter['payment_status'] == 'delivered' ? 'selected' : '' }}>
                                {{ __('pharma::messages.delivered') }}</option>
                            <option value="cancelled" {{ $filter['payment_status'] == 'cancelled' ? 'selected' : '' }}>
                                {{ __('pharma::messages.cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap border rounded">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">

                </div>
            </x-slot>

        </x-backend.section-header>



        <table id="datatable" class="table table-responsive">
        </table>
    </div>


    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="OrderDetailOffcanvas"
        aria-labelledby="orderDetailsLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="orderDetailsLabel">{{ __('pharma::messages.edit_order') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" id="orderDetailsContent">
            {{-- Content loaded via AJAX --}}
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end offcanvas-w-30" tabindex="-1" id="ViewOrderDetailOffcanvas"
        aria-labelledby="viewOrderDetailsLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="viewOrderDetailsLabel">{{ __('pharma::messages.order_details') }}</h5>

            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body" id="viewOrderDetailsContent">

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
        const canDeletePurchaseOrder = @json(auth()->user()->can('delete_purchased_order'));

        const columns = [];

        if (canDeletePurchaseOrder) {
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
            data: 'created_at',
            name: 'created_at',
            title: "{{ __('pharma::messages.date') }}"
        }, {
            data: 'medicine.name',
            name: 'medicine.name',
            title: "{{ __('pharma::messages.medicine_name') }}"
        }, {
            data: 'medicine.supplier.name',
            name: 'medicine.supplier.name',
            title: "{{ __('pharma::messages.supplier_name') }}",
            render: function(data, type, row) {
                return data ?? '-';
            }
        }, {
            data: 'medicine.manufacturer.name',
            name: 'medicine.manufacturer.name',
            title: "{{ __('pharma::messages.manufacturer_name') }}"
        }, {
            data: 'quantity',
            name: 'quantity',
            title: "{{ __('pharma::messages.quantity') }}"
        }, {
            data: 'delivery_date',
            name: 'delivery_date',
            title: "{{ __('pharma::messages.delivery_date') }}"
        }, {
            data: 'payment_status',
            name: 'payment_status',
            title: "{{ __('pharma::messages.payment_status') }}"
        }, {
            data: 'order_status',
            name: 'order_status',
            title: "{{ __('pharma::messages.order_status') }}",
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

            initDatatable({
                url: '{{ route('backend.order-medicine.index_data') }}',
                finalColumns,
                advanceFilter: () => {},
                orderColumn: [
                    [1, "desc"]
                ],
            });

            $(document).on('change', 'select[name="payment_status"]', function() {
                const selectedStatus = $(this).val();
                const orderId = $(this).data('order-id');

                console.log('Selected Status:', selectedStatus);
                console.log('Order ID:', orderId);

                $.ajax({
                    url: '{{ route('backend.order-medicine.update_payment_status') }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        payment_status: selectedStatus,
                        order_id: orderId
                    },
                    success: function(data) {

                        window.renderedDataTable.ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {

                    }
                });
            });
            $(document).on('change', 'select[name="order_status"]', function() {
                const selectedStatus = $(this).val();
                const orderId = $(this).data('order-id');

                console.log('Selected Status:', selectedStatus);
                console.log('Order ID:', orderId);

                $.ajax({
                    url: '{{ route('backend.order-medicine.update_order_status') }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        order_status: selectedStatus,
                        order_id: orderId
                    },
                    success: function(data) {

                        window.renderedDataTable.ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {

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

        $(document).on('click', '.edit-order-medicine-btn', function() {
            let orderId = $(this).data('id');
            $('#orderDetailsContent').html(
                '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );
            $('#OrderDetailOffcanvas').offcanvas('show');
            const orderMedicineEditUrl =
                "{{ route('backend.order-medicine.edit', ['order_medicine' => '__ID__']) }}";
            let url = orderMedicineEditUrl.replace('__ID__', orderId);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#orderDetailsContent').html(response
                        .html); // return HTML partial from controller
                },
                error: function() {
                    $('#orderDetailsContent').html(
                        '<p class="text-danger">Failed to load supplier details.</p>');
                }
            });
        });

        $(document).on('click', '.view-order-medicine-btn', function() {
            let orderId = $(this).data('id');
            $('#viewOrderDetailsContent').html(
                '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );
            $('#ViewOrderDetailOffcanvas').offcanvas('show');
            const orderMedicineViewUrl =
                "{{ route('backend.order-medicine.show', ['order_medicine' => '__ID__']) }}";
            let url = orderMedicineViewUrl.replace('__ID__', orderId);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#viewOrderDetailsContent').html(response
                        .html); // return HTML partial from controller
                },
                error: function() {
                    $('#viewOrderDetailsContent').html(
                        '<p class="text-danger">Failed to load supplier details.</p>');
                }
            });
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
