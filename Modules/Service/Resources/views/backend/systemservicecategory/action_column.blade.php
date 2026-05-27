<div class="d-flex gap-3 align-items-center">
    {{-- Edit specialization: Admin (Full), Clinic Admin (No), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <button type="button" class="btn text-primary p-0 fs-5" data-crud-id="{{ $data->id }}"
            data-parent-id="{{ $data->parent_id }}" data-bs-toggle="tooltip" title="{{ __('messages.edit') }}"> <i
                class="ph ph-pencil-simple-line"></i> </button>
    @endif
    {{-- Delete specialization: Admin (Full), Clinic Admin (No), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route("backend.$module_name.destroy", $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" data-confirm="{{ __('messages.are_you_sure?') }}">
            <i class="ph ph-trash"></i> </a>
    @endif
</div>
