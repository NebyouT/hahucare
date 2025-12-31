@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
<div class="table-content mb-3">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">

        </div>

        <x-slot name="toolbar">
            <div>
                <div class="datatable-filter border rounded">
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                        <option value="">{{ __('messages.all') }}</option>
                        <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>{{ __('messages.unpaid') }}</option>
                        <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>{{ __('messages.paid') }}</option>
                    </select>
                </div>
            </div>

            <div class="input-group flex-nowrap border rounded">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
            </div>
            <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i class="ph ph-funnel"></i>{{__('messages.advance_filter')}}</button>
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
        <select id="patient" name="patient" data-filter="select"
            class="select2 form-control"
            data-ajax--url="{{ route('backend.get_search_data', ['type' => 'customers']) }}"
            data-ajax--cache="true"
            data-placeholder="{{ __('pharma::messages.select_patient') }}">
            <option value="">{{ __('pharma::messages.select_patient') }}</option>
        </select>
    </div>

     <div class="form-group datatable-filter">
        <label class="form-label" for="service"> {{ __('pharma::messages.service') }}</label>
        <select id="service" name="service" data-filter="select"
            class="select2 form-control"
            data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinics_services']) }}"
            data-ajax--cache="true"
            data-placeholder="{{ __('pharma::messages.select_service') }}">
            <option value="">{{ __('pharma::messages.select_service') }}</option>
        </select>
    </div>

    <div class="form-group datatable-filter">
        <label class="form-label" for="payment_status"> {{ __('pharma::messages.payment_status') }}</label>
        <select id="payment_status" name="payment_status" class="form-control select2" data-filter="select"
            data-placeholder="{{ __('pharma::messages.select_payment_status') }}">
            <option value="">{{ __('pharma::messages.select_payment_status') }}</option>
            <option value="0">{{ __('messages.unpaid') }}</option>
            <option value="1">{{ __('messages.paid') }}</option>
        </select>
    </div>

    <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
</x-backend.advance-filter>

@endsection

@push ('after-styles')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push ('after-scripts')
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

<script type="text/javascript" defer>
    const columns = [
        {
            data: 'id',
            name: 'id',
            title: "{{ __('pharma::messages.encounter_id') }}"
        },
        {
            data: 'created_at',
            name: 'created_at',
            title: "{{ __('pharma::messages.date_time') }}"
        },
        {
            data: 'patient',
            name: 'patient',
            title: "{{ __('pharma::messages.patient') }}"
        },
        {
            data: 'service',
            name: 'service',
            title: "{{ __('pharma::messages.service') }}"
        },
        {
            data: 'total_medicine',
            name: 'total_medicine',
            title: "{{ __('pharma::messages.total_medicine') }}"
        },
        {
            data: 'total_amount',
            name: 'total_amount',
            title: "{{ __('messages.total_amount') }}"
        },

        {
            data: 'prescription_payment_status',
            name: 'prescription_payment_status',
            title: "{{ __('messages.payment_status') }}",
            orderable: false,
            searchable: true,
            width: '5%'
        }
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
            url: '{{ route("backend.pharma.billing-records.index_data") }}',
            finalColumns,
            advanceFilter: () => {
                return {
                    patient: $('#patient').val(),
                    service: $('#service').val(),
                    payment_status: $('#payment_status').val(),
                };
            },
            orderColumn: [
                [1, "desc"]
            ],
        });
    });

    $('#reset-filter').on('click', function(e) {
        $('#patient').val('');
        $('#service').val('');
        $('#payment_status').val('');
        window.renderedDataTable.ajax.reload(null, false);
    });


</script>

@endpush
