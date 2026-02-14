@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
<div class="table-content mb-3">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            @php
                $permissionsToCheck = ['edit_medicine_form', 'delete_medicine_form'];
            @endphp
        
            @if(collect($permissionsToCheck)->contains(fn ($permission) => auth()->user()->can($permission)) || auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
                <x-backend.quick-action url="{{ route('backend.supplier-type.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->can('edit_medicine_form'))
                                <option value="change-status">{{ __('messages.status') }}</option>
                            @endif
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->can('delete_medicine_form'))
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
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                        <option value="">{{ __('messages.all') }}</option>
                        <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                        <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    </select>
                </div>
            </div>
            <div class="input-group flex-nowrap border rounded">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search gap-1 " placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
               
            </div>
             <a href="{{ route('backend.supplier-type.create') }}" class="btn btn-primary ms-3">
                    <i class="ph ph-plus-circle"></i> {{ __('messages.new') }}
                </a>
        </x-slot>
    </x-backend.section-header>

    <table id="datatable" class="table table-responsive">
    </table>
</div>
@endsection

@push ('after-styles')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push ('after-scripts')
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<script type="text/javascript" defer>
    const columns = [
        {
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
            title: "{{ __('messages.name') }}"
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('messages.status') }}",
            orderable: true,
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
        // Initialize Select2 for quick action dropdowns
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
            url: '{{ route("backend.supplier-type.index_data") }}',
            finalColumns,
            advanceFilter: () => {},
            orderColumn: [
                [1, "asc"]
            ],
        });
    });
    
    function resetQuickAction () {
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

        $('#quick-action-type').change(function () {
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
