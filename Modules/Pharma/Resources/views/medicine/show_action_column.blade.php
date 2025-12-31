<div class="d-flex gap-3 align-items-center">
    <button type="button" 
        data-id="{{ $data->id }}"
        supplier-id="{{ $data->supplier_id }}"
        data-url="{{ route('backend.medicine.medicine-details', ['medicine_id' => $data->id, 'supplier_id' => $data->supplier_id]) }}"
        class="btn text-primary p-0 fs-5 view-medicine-btn" 
        title="{{ __('messages.view') }}" data-bs-toggle="tooltip">
        <i class="ph ph-eye align-middle"></i>
    </button>
</div>