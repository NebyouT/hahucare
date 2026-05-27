<div class="d-flex gap-3 align-items-center">
    {{-- Edit doctor session: Admin (Full), Clinic Admin (Own Clinics), Doctor (Own Sessions), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor') || (auth()->user()->hasRole('doctor') && $data->id == auth()->id()))
    <button type="button" data-assign-module="{{ $data->id }}" data-assign-target="#session-form-offcanvas"
        data-assign-event="employee_assign" class="btn text-success p-0 fs-6" data-bs-toggle="tooltip"
        title="{{ __('messages.session') }}">
        <i class="ph ph-clock-user"></i>
    </button>
    @endif

    {{-- View doctor profile: Admin (Full), Clinic Admin (Own Clinics), Doctor (Self), Receptionist (Limited), Pharmacist (Limited), Lab Technologist (Limited) --}}
    <button type="button" class="btn text-danger p-0 fs-6" data-bs-toggle="offcanvas"
        data-bs-target="#doctor-details-form-offcanvas" data-doctor-id="{{ $data->id }}"
        data-url="{{ route('backend.doctor.doctor.detail', ['id' => $data->id]) }}" title="{{ __('clinic.view') }}"
        onclick="fetchDoctorDetailsAndOpenOffcanvas(this)">
        <i class="ph ph-eye align-middle"></i>
    </button>
    <script>
        function fetchDoctorDetailsAndOpenOffcanvas(btn) {
            var doctorId = btn.getAttribute('data-doctor-id');
            var url = btn.getAttribute('data-url') || ('/app/doctor/detail/' + doctorId); // fallback
            var offcanvasSelector = btn.getAttribute('data-bs-target');
            var offcanvasEl = document.querySelector(offcanvasSelector);

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var newBody = doc.querySelector('.offcanvas-body');
                    var targetBody = offcanvasEl.querySelector('.offcanvas-body');
                    if (newBody && targetBody) {
                        targetBody.innerHTML = newBody.innerHTML;
                    } else {
                        offcanvasEl.innerHTML = doc.body.innerHTML;
                    }
                    var bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    bsOffcanvas.show();
                })
                .catch(() => alert('{{ __('clinic.failed_to_load_doctor_details') }}'));
        }
    </script>
    {{-- Change doctor password: Admin (Full), Clinic Admin (Own Clinics), Doctor (Self), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor') || (auth()->user()->hasRole('doctor') && $data->id == auth()->id()))
        <button type="button" class="btn p-0 fs-6 text-info" data-bs-toggle="offcanvas"
            data-bs-target="#doctor_change_password" data-doctor-id="{{ $data->id }}"
            title="{{ __('employee.change_password') }}">
            <i class="ph ph-key align-middle"></i>
        </button>
    @endif

    {{-- Edit doctor profile: Admin (Full), Clinic Admin (Own Clinics), Doctor (Self), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || auth()->user()->hasRole('vendor') || (auth()->user()->hasRole('doctor') && $data->id == auth()->id()))
        <button type="button" class="btn text-success p-0 fs-5" data-id="{{ $data->id }}" data-mode="edit"
            data-bs-toggle="offcanvas" data-bs-target="#form-offcanvas" title="{{ __('clinic.edit_doctor') }}">
            <i class="ph ph-pencil-simple-line align-middle"></i>
        </button>
    @endif

    {{-- Delete doctor: Admin (Full), Clinic Admin (Limited), Doctor (No), Receptionist (No), Pharmacist (No), Lab Technologist (No) --}}
    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
        <a href="{{ route("backend.$module_name.destroy", $data->id) }}"
            id="delete-{{ $module_name }}-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax"
            data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
            title="{{ __('messages.delete') }}"
            data-confirm="{{ __('messages.are_you_sure?', ['form' => $data->full_name ?? __('Unknown'), 'module' => __('appointment.lbl_doctor')]) }}">
            <i class="ph ph-trash align-middle"></i>
        </a>
    @endif

    @if ($customform)
        @foreach ($customform as $form)
            @php
                $formdata = json_decode($form->formdata);
                $clinic = json_decode($form->appointment_status);
            @endphp
            <button type="button" data-assign-target="#customform-offcanvas" data-assign-event="custom_form_assign"
                data-appointment-type="doctor" data-appointment-id="{{ $data->id }}"
                data-form-id="{{ $form->id }}" class="btn text-info p-0 fs-5" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{ $formdata->form_title }}" onclick="dispatchCustomEvent(this)">
                <i class="icon ph ph-file align-middle"></i>
            </button>
        @endforeach
    @endif
</div>
