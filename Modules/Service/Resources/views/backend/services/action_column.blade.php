<div class="d-flex gap-3 align-items-center">
  {{-- Gallery button - Admin only --}}
  @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
    <button type='button' data-gallery-module="{{ $data->id }}" data-gallery-target='#service-gallery-form' data-gallery-event='service_gallery' class='btn text-info p-0 fs-5 rounded text-nowrap' data-bs-toggle="tooltip" title="{{ __('messages.gallery_for_service') }}"><i class="ph ph-image"></i></button>
  @endif
  {{-- Edit service: Admin (Full), Clinic Admin (Own Clinics), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
  @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor'))
      <button type="button" class="btn text-primary p-0 fs-5" data-crud-id="{{$data->id}}" title="{{ __('messages.edit') }} " data-bs-toggle="tooltip"> <i class="ph ph-pencil-simple-line"></i></button>
  @endif
  {{-- Delete service: Admin (Full), Clinic Admin (Limited), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
  @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
      <a href="{{route("backend.$module_name.destroy", $data->id)}}" id="delete-{{$module_name}}-{{$data->id}}" class="btn text-danger p-0 fs-5" data-type="ajax" data-method="DELETE" data-token="{{csrf_token()}}" data-bs-toggle="tooltip" title="{{__('messages.delete')}}" data-confirm="{{ __('messages.are_you_sure?') }}"> <i class="ph ph-trash"></i></a>
  @endif
</div>
