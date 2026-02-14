<div class="d-flex gap-3 align-items-center">
    <button type="button" data-id="{{ $data->id }}" class="btn text-secondary p-0 fs-5 view-pharma-btn"
        data-url="{{ route('backend.pharma.detail', $data->id) }}" title="{{ __('messages.view') }}"
        data-bs-toggle="tooltip">
        <i class="ph ph-eye align-middle"></i>
    </button>

    @if (!auth()->user()->hasRole('receptionist'))
        <button type="button" data-id="{{ $data->id }}" class="btn text-primary p-0 fs-5 change-pharma-password-btn"
            data-url="{{ route('backend.pharma.change-password', $data->id) }}"
            title="{{ __('messages.change_password') }}" data-bs-toggle="tooltip">
            <i class="ph ph-key align-middle"></i>
        </button>

        <a href="{{ route('backend.pharma.edit', $data->id) }}" class="btn text-success p-0 fs-5"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line align-middle"></i>
        </a>
    @endif

</div>
