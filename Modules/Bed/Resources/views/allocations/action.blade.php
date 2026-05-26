<?php $auth_user = authSession(); ?>

<div class="d-flex justify-content-end align-items-center gap-2">
    {{-- Edit allocation: Admin (Full), Vendor (Own Clinics), Doctor (Assigned Patients), Receptionist (Limited), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor') || (auth()->user()->hasRole('doctor') && $allocation->patientEncounter && $allocation->patientEncounter->doctor_id == auth()->id()) || auth()->user()->hasRole('receptionist'))
        <a class="btn text-success p-0 fs-5" href="{{ route('backend.bed-allocation.edit', $allocation->id) }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
    @endif
    {{-- Delete allocation: Admin (Full), Vendor (Limited), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route('backend.bed-allocation.destroy', $allocation->id) }}" id="delete-bed-allocation-{{ $allocation->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
            data-confirm="Are you sure you want to delete bed allocation for &quot;{{ $allocation->patient ? ($allocation->patient->first_name . ' ' . $allocation->patient->last_name) : 'Patient' }}&quot;?">
            <i class="ph ph-trash"></i>
        </a>
    @elseif (auth()->user()->hasRole('vendor'))
        {{-- Vendor has limited delete - only for their own clinics --}}
        @if ($allocation->clinic_admin_id == auth()->id())
            <a href="{{ route('backend.bed-allocation.destroy', $allocation->id) }}" id="delete-bed-allocation-{{ $allocation->id }}"
                class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
                data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
                data-confirm="Are you sure you want to delete bed allocation for &quot;{{ $allocation->patient ? ($allocation->patient->first_name . ' ' . $allocation->patient->last_name) : 'Patient' }}&quot;?">
                <i class="ph ph-trash"></i>
            </a>
        @endif
    @endif
</div>

