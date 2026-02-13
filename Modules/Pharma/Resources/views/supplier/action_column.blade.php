<div class="d-flex gap-3 align-items-center">
    <button type="button" data-id="{{ $data->id }}" class="btn text-secondary p-0 fs-5 view-supplier-btn"
        data-bs-toggle="tooltip" title="{{ __('messages.view') }}">
        <i class="ph ph-eye align-middle"></i>
    </button>
    @if (auth()->user()->hasRole(['admin', 'demo_admin', 'vendor']) || auth()->user()->can('edit_suppliers'))
        <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{ $data->id }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"
            onclick="window.location.href='{{ route('backend.suppliers.edit', $data->id) }}'">
            <i class="ph ph-pencil-simple-line align-middle"></i>
        </button>
    @endif

</div>
