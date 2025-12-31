<div class="d-flex gap-3 align-items-center">
    <button type="button" data-id="{{ $data->id }}" class="btn text-secondary p-0 fs-5"
        onclick="window.location.href='{{ route('backend.prescription.show', $data->id) }}'" data-bs-toggle="tooltip"
        title="{{ __('messages.view') }}">
        <i class="ph ph-eye align-middle"></i>
    </button>
    @if (
        (auth()->user()->hasRole(['admin', 'demo_admin']) ||
            auth()->user()->can('edit_prescription')) &&
            $data->billingrecord->payment_status == 1)
    @endif
    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('delete_prescription'))
        <a href="{{ route('backend.prescription.destroy', $data->id) }}"
            id="delete-{{ $module_name }}-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax"
            data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            title="{{ __('messages.delete') }}" data-confirm="{{ __('messages.are_you_sure?') }}">
            <i class="ph ph-trash align-middle"></i></a>
    @endif
</div>
