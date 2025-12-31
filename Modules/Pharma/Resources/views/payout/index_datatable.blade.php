@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <x-backend.section-header>
            <x-slot name="toolbar">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">
                </div>
            </x-slot>
        </x-backend.section-header>

        <table id="datatable" class="table table-responsive">
        </table>
    </div>
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        const columns = [

            {
                data: 'created_at',
                name: 'created_at',
                title: "{{ __('pharma::messages.date') }}"
            },
            {
                data: 'payment_type',
                name: 'payment_type',
                title: "{{ __('pharma::messages.payment_type') }}"
            },
            {
                data: 'payout_amount',
                name: 'payout_amount',
                title: "{{ __('pharma::messages.payout_amount') }}"
            }
        ];


        let finalColumns = [
            ...columns,

        ];

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route('backend.pharma.billing-records.index_data') }}',
                finalColumns,
                advanceFilter: () => {},
                orderColumn: [
                    [1, "asc"]
                ],
            });
        });
    </script>
@endpush
