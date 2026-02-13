@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    <div class="table-content mb-5">

            <table id="datatable" class="table table-responsive">
            </table>
    </div>

    
    <div class="offcanvas offcanvas-end offcanvas-w-20" tabindex="-1" id="pharmaPayoutOffcanvas" aria-labelledby="pharmaPayoutOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="pharmaPayoutOffcanvasLabel">{{ __('pharma::messages.pharma_payout_detail') }}</h5>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body" id="pharmaPayoutContent">
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
    
    <div class="offcanvas offcanvas-end offcanvas-w-20" tabindex="-1" id="view_commission_list" aria-labelledby="viewCommissionLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="viewCommissionLabel">{{ __('pharma::messages.pharma_commission_details') }}</h5>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body" id="viewCommissionContent">
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
@endsection
@push ('after-styles')
<link rel="stylesheet" href="{{ mix('modules/earning/style.css') }}">
<!-- DataTables Core and Extensions -->
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push ('after-scripts')
<script src="{{ mix('modules/earning/script.js') }}"></script>
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>

<!-- DataTables Core and Extensions -->
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript" defer>
        const columns = [ 
            {
                data: 'user_id',
                name: 'user_id',
                title: "{{ __('earning.lbl_name') }}",
                orderable: true, 
                searchable: true
            },
            { 
                data: 'total_prescription', 
                name: 'total_prescription', 
                title: "{{ __('earning.total_prescription') }}", 
                orderable: false,
                searchable: false
            },
            { 
                data: 'total_service_amount', 
                name: 'total_service_amount', 
                title: "{{ __('earning.lbl_total_earning') }}", 
                orderable: false,
                searchable: false
            },
            { 
                data: 'total_commission_earn', 
                name: 'total_commission_earn', 
                title: "{{ __('earning.lbl_total_commission') }}", 
                orderable: false,
                searchable: false
            },
            { 
                data: 'total_admin_earning', 
                name: 'total_admin_earning', 
                title: "{{ __('earning.lbl_admin_earnings') }}", 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'total_pay', 
                name: 'total_pay', 
                title: "{{ __('earning.lbl_pharma_earnings') }}",
                orderable: false,
                searchable: false
            },
            
            {
                data: 'updated_at',
                name: 'updated_at',
                title: "{{ __('service.lbl_update_at') }}",
                orderable: true,
                visible: false,
           },

        ]


        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('service.lbl_action') }}",
            width: '5%'
        }]


        let finalColumns = [
            ...columns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                orderColumn: [[6, "desc"]],
                advanceFilter: () => {
                    return {
                    }
                }
            });

            $(document).on('click', '.pharma-payout-btn', function() {
                var pharmaId = $(this).data('id');

                $('#pharmaPayoutContent').html('<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>');
                var offcanvasElement = document.getElementById('pharmaPayoutOffcanvas');
                var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
                offcanvas.show();
                $.ajax({
                    url: `{{ route('backend.earning.payout_details', '') }}/${pharmaId}`,
                    type: 'GET',
                    success: function(response) {
                        $('#pharmaPayoutContent').html(response.html);
                        $('#payment_method').select2();
                    },
                    error: function() {
                        $('#pharmaPayoutContent').html('<p class="text-danger">{{ __("messages.failed_to_load_payout_details") }}</p>');
                    }
                });
            });

            $(document).on('submit', '#earningForm', function(e) {
                e.preventDefault();
                var form = this;
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    e.preventDefault();
                    submitEarningFormAjax($(form));
                }
                $(form).addClass('was-validated');

            });

            function submitEarningFormAjax($form) {
                var $submitBtn = $form.find('#saveBtn');
                $submitBtn.prop('disabled', true);
                $submitBtn.find('.btn-text').addClass('d-none');
                $submitBtn.find('#saveLoader').removeClass('d-none');

                $.ajax({
                    url: $form.attr('action'),
                    method: $form.attr('method'),
                    data: $form.serialize(),
                    success: function(response) {
                        var offcanvasEl = document.querySelector('#earningForm').closest('.offcanvas');
                        $('#datatable').DataTable().ajax.reload(null, false);
                        if (offcanvasEl) {
                            var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                            if(bsOffcanvas) bsOffcanvas.hide();
                        }
                    },
                    error: function(xhr) {
                        alert('Error occurred, please try again.');
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.find('.btn-text').removeClass('d-none');
                        $submitBtn.find('#saveLoader').addClass('d-none');
                    }
                });
            }


            $(document).on('click', '.view-commission-details', function() {
                const pharmaId = $(this).data('id');
                const url = $(this).data('url');
                $('#viewCommissionContent').html('<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>');


                const viewCanvas = new bootstrap.Offcanvas('#view_commission_list');
                viewCanvas.show();

               
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { id: pharmaId, type: 'pharma_commission' },
                    success: function(response) {
                        $('#viewCommissionContent').html(response.html);
                    },
                    error: function() {
                        $('#viewCommissionContent').html('<p class="text-danger">{{ __("messages.failed_to_load_commission_data") }}</p>');
                    }
                });
            });


            

        })
</script>
@endpush
