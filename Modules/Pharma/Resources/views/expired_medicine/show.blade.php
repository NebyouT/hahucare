@extends('pharma::layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Medicine Details --}}
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary text-white">{{ $medicine->category->name }}</span>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between text-body">
                                    <div class="p-2">
                                        <small>{{ __('pharma::messages.medicine_name') }}:</small><br>
                                        <strong class="text-heading">{{ $medicine->name }}</strong>
                                    </div>
                                    <div class="p-2">
                                        <small>{{ __('pharma::messages.dosage') }}:</small><br>
                                        <strong class="text-heading">{{ $medicine->dosage }}</strong>
                                    </div>
                                    <div class="p-2">
                                        <small>{{ __('pharma::messages.form') }}:</small><br>
                                        <strong class="text-heading">{{ $medicine->form->name }}</strong>
                                    </div>
                                    <div class="p-2">
                                        <small>{{ __('pharma::messages.expiry_date') }}:</small><br>
                                        <strong
                                            class="text-heading">{{ \Carbon\Carbon::parse($medicine->expiry_date)->format('Y-m-d') }}</strong>
                                    </div>
                                    <div class="p-2">
                                        <small>{{ __('pharma::messages.total_price') }}:</small><br>
                                        <strong
                                            class="text-heading">${{ number_format($medicine->total_price, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- Supplier List --}}

                        <h5 class="mb-3">{{ __('pharma::messages.supplier_list') }}</h5>
                        <div class="table-responsive">

                            <table id="datatable" class="table table-responsive">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="medicineDetailsOffcanvas"
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
                url: '{{ route('backend.medicine.medicine-detail-table') }}',
                finalColumns,
                advanceFilter: () => {
                    return {
                        medicine_id: '{{ $medicine->id }}' // pass the current medicine ID
                    };
                },
                orderColumn: [
                    [1, "asc"]
                ],
            });


            $(document).on('click', '.view-medicine-btn', function() {
                let medicineId = $(this).data('id');
                let supplierId = $(this).attr('supplier-id');
                $('#expiredMedicineDetailsContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#medicineDetailsOffcanvas').offcanvas('show');

                $.ajax({
                    url: `/app/medicine/medicine-details/${medicineId}/${supplierId}`, // adjust your route
                    type: 'GET',
                    success: function(response) {
                        console.log(response);
                        $('#expiredMedicineDetailsContent').html(response
                            .html); // return HTML partial from controller
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
