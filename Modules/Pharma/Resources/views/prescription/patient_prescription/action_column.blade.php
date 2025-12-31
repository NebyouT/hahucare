@if (!($prescriptionStatus == 1 || $paymentStatus == 1))
    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('edit_prescription'))
        <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{ $data->id }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"
            onclick="window.location.href='{{ route('backend.prescription.patient-prescription.edit', $data->id) }}'">
            <i class="ph ph-pencil-simple-line align-middle"></i>
        </button>
    @endif
    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('delete_prescription'))
        <a href="{{ route('backend.prescription.patient-prescription.destroy', $data->id) }}"
            id="delete-{{ $module_name }}-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax"
            data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            title="{{ __('messages.delete') }}" data-encounter-id="{{ $data->encounter_id }}"
            data-confirm="{{ __('messages.are_you_sure?') }}">
            <i class="ph ph-trash align-middle"></i></a>
    @endif
@endif

</div>
