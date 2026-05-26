<div class="text-end d-flex gap-3 align-items-center">
    {{-- Edit observation list: Admin (Full), Clinic Admin (No), Doctor (Own Patients), Receptionist (Vitals Entry), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || (auth()->user()->hasRole('doctor') && $data->created_by == auth()->id()) || auth()->user()->hasRole('receptionist'))
        <button type="button" class="btn text-success p-0 fs-5" data-crud-id="{{ $data->id }}"
            title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line"></i> </button>
    @endif
    {{-- Delete observation list: Admin (Full), Clinic Admin (No), Doctor (Own Patients), Receptionist (Vitals Entry), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route('backend.observation.destroy', $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $data->name ?? __('Unknown'), 'module' => __('appointment.observation')]) }}">
            <i class="ph ph-trash"></i></a>
    @elseif (auth()->user()->hasRole('doctor') && $data->created_by == auth()->id())
        <a href="{{ route('backend.observation.destroy', $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $data->name ?? __('Unknown'), 'module' => __('appointment.observation')]) }}">
            <i class="ph ph-trash"></i></a>
    @elseif (auth()->user()->hasRole('receptionist'))
        {{-- Receptionist - Vitals Entry only (can delete vitals-related observations) --}}
        <a href="{{ route('backend.observation.destroy', $data->id) }}" id="delete-{{ $module_name }}-{{ $data->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $data->name ?? __('Unknown'), 'module' => __('appointment.observation')]) }}">
            <i class="ph ph-trash"></i></a>
    @endif
</div>
