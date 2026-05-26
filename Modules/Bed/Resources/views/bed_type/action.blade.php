<?php
$auth_user = authSession();
?>
<div class="d-flex justify-content-end align-items-center">
    {{-- Edit bed type: Admin (Full), Vendor (Own Clinics), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor'))
        <a class="btn text-success p-0 fs-5" href="{{ route('backend.bed-type.edit', $bed->id) }}"
            title="{{ __('messages.edit') }}" data-bs-toggle="tooltip"><i class="ph ph-pencil-simple-line"></i></a>
    @endif
    {{-- Delete bed type: Admin (Full), Vendor (Limited), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technician (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route('backend.bed-type.destroy', $bed->id) }}" id="delete-bed_type-{{ $bed->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
            data-confirm="Are you sure you want to delete bed type &quot;{{ $bed->type }}&quot;?">
            <i class="ph ph-trash"></i>
        </a>
    @elseif (auth()->user()->hasRole('vendor'))
        {{-- Vendor has limited delete - only for their own clinics --}}
        {{-- Bed types are global, so vendor can delete if they have permission --}}
        <a href="{{ route('backend.bed-type.destroy', $bed->id) }}" id="delete-bed_type-{{ $bed->id }}"
            class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{ csrf_token() }}"
            data-bs-toggle="tooltip" title="{{ __('messages.delete') }}" 
            data-confirm="Are you sure you want to delete bed type &quot;{{ $bed->type }}&quot;?">
            <i class="ph ph-trash"></i>
        </a>
    @endif
</div>
