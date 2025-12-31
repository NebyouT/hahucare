<div class="d-flex gap-3 align-items-center">
    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('edit_medicine'))
        <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{ $data->id }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"
            onclick="window.location.href='{{ route('backend.medicine.edit', $data->id) }}'">
            <i class="ph ph-pencil-simple-line align-middle"></i>
        </button>
    @endif

    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('view_medicine'))
        <button type="button" class="btn text-secondary p-0 fs-5" style="color: #6c757d;"
            data-crud-id="{{ $data->id }}" title="{{ __('messages.show') }}: {{ $data->name ?? '' }}"
            data-bs-toggle="tooltip" onclick="window.location.href='{{ route('backend.medicine.show', $data->id) }}'">
            <i class="ph ph-notepad align-middle"></i>
        </button>
    @endif
    <button type="button" class="btn p-0 fs-5 text-primary order-medicine-btn" data-id="{{ $data->id }}"
        data-crud-id="{{ $data->id }}" title="{{ __('messages.order') }}" data-bs-toggle="tooltip">
        <i class="ph ph-shopping-cart-simple align-middle"></i>
    </button>
</div>
