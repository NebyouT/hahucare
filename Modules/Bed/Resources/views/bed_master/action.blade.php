<?php
$auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center gap-2">
    {{-- Edit bed: Admin (Full), Vendor (Own Clinics), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor'))
        <a class="btn text-success p-0 fs-5" href="{{ route('backend.bed-master.edit', $bedMaster->id) }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
            <i class="ph ph-pencil-simple-line"></i>
        </a>
    @endif
    {{-- Delete bed: Admin (Full), Vendor (Limited), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route('backend.bed-master.destroy', $bedMaster->id) }}" id="delete-bed-master-{{ $bedMaster->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
            data-confirm="Are you sure you want to delete bed &quot;{{ $bedMaster->bed }}&quot;?">
            <i class="ph ph-trash"></i>
        </a>
    @elseif (auth()->user()->hasRole('vendor'))
        {{-- Vendor has limited delete - only for their own clinics --}}
        @if ($bedMaster->clinic_admin_id == auth()->id())
            <a href="{{ route('backend.bed-master.destroy', $bedMaster->id) }}" id="delete-bed-master-{{ $bedMaster->id }}"
                class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
                data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
                data-confirm="Are you sure you want to delete bed &quot;{{ $bedMaster->bed }}&quot;?">
                <i class="ph ph-trash"></i>
            </a>
        @endif
    @endif
</div>

