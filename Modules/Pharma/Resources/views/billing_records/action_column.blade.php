<div class="d-flex gap-3 align-items-center">
    <button type="button" 
        data-id="{{ $data->id }}"
        class="btn text-secondary p-0 fs-5"
        onclick="window.location.href='{{ route('backend.pharma.billing-records.billing_detail', $data->id) }}'"
        title="View"
        data-bs-toggle="tooltip">
        <i class="ph ph-file-text align-middle"></i>
    </button>
</div>