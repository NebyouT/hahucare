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
                    <x-backend.quick-action url="{{ route('backend.prescription.bulk_action') }}">
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

            </div>

            <x-slot name="toolbar">

                <div class="input-group flex-nowrap border rounded">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">
                </div>

            </x-slot>

        </x-backend.section-header>

        <table id="datatable" class="table table-responsive">
        </table>
        <!-- Static Add Extra Medicine & Payment Detail Section -->
        @if (!($prescriptionStatus == 1 || $paymentStatus == 1))
            <div class="d-flex justify-content-between align-items-center mt-5 bg-gray-900 p-3">
                <h6 class="fw-bold mb-0">{{ __('pharma::messages.add_extra_medicine') }}</h6>
                <a href="{{ route('backend.prescription.add_extra_medicine', $id) }}"
                    class="bg-primary text-white text-decoration-none px-3 py-2 rounded">
                    {{ __('pharma::messages.add_medicine') }}
                </a>
            </div>
        @endif


        <h6 class="fw-bold mt-5">{{ __('pharma::messages.payment_detail') }}</h6>
        <div id="payment-detail-section">

        </div>


    </div>

    <div class="offcanvas offcanvas-w-40" tabindex="-1" id="supplierDetailsOffcanvas"
        aria-labelledby="supplierDetailsLabel">
        <div class="offcanvas-header mb-5 border-bottom-gray-700">
            <h5 class="mb-0" id="supplierDetailsLabel">{{ __('pharma::messages.supplier_details') }}</h5>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
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
        const prescriptionId = @json($id);
        const paymentDetailRoute = @json(route('backend.prescription.payment_detail', ['id' => '___ID___']));

        const prescriptionStatus = {{ $prescriptionStatus ?? 0 }};
        const paymentStatus = {{ $paymentStatus ?? 0 }};

        window.afterPrescriptionDelete = function(prescriptionId) {
            console.log(prescriptionId);
            $.ajax({
                url: "{{ route('backend.prescription.payment_detail', ['id' => '__ID__']) }}".replace('__ID__',
                    prescriptionId),
                type: 'GET',
                success: function(response) {
                    $('#payment-detail-section').html(response);
                }
            });
        }


        const columns = [{
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                width: '0%',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            {
                data: 'name',
                name: 'name',
                title: "{{ __('pharma::messages.medicine_name') }}"
            },
            {
                data: 'category',
                name: 'category',
                title: "{{ __('pharma::messages.category') }}"
            },
            {
                data: 'form',
                name: 'form',
                title: "{{ __('pharma::messages.form') }}"
            },
            {
                data: 'duration',
                name: 'duration',
                title: "{{ __('pharma::messages.days') }}"
            },
            {
                data: 'frequency',
                name: 'frequency',
                title: "{{ __('pharma::messages.frequency') }}"
            },
            {
                data: 'quantity',
                name: 'quantity',
                title: "{{ __('pharma::messages.quantity') }}"
            },
            {
                data: 'price',
                name: 'price',
                title: "{{ __('pharma::messages.price') }}"
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

        let finalColumns;

        if (!(prescriptionStatus == 1 || paymentStatus == 1)) {
            finalColumns = [...columns, ...actionColumn];
        } else {
            finalColumns = [...columns];
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route('backend.prescription.user_prescription_detail') }}',
                finalColumns,
                advanceFilter: () => {
                    return {
                        'prescription_id': prescriptionId,
                    };
                },
                orderColumn: [
                    [1, "asc"]
                ],
            });

            reloadPaymentDetail(prescriptionId);

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

        function reloadPaymentDetail(encounterId) {
            const routeUrl = paymentDetailRoute.replace('___ID___', encounterId);

            $.ajax({
                url: routeUrl,
                type: 'GET',
                success: function(response) {
                    $('#payment-detail-section').html(response);
                },
                error: function() {
                    alert('Failed to reload payment details.');
                }
            });
        }
    </script>
@endpush
