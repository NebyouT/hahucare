@php
    $currencySymbol  = \App\Models\Currency::defaultSymbol();
    $referralPatient = $referral->patient;
    $referralDoctor  = $referral->referredToDoctor;
    $referralClinic  = $doctorClinic;
@endphp

@extends('backend.layouts.app')

@section('title', __('patientreferral::messages.book_appointment'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.patientreferral.index') }}">Referrals</a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.patientreferral.show', $referral) }}">#{{ $referral->id }}</a></li>
        <li class="breadcrumb-item active">Book Appointment</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            {{-- Referral summary --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="ph ph-user-circle me-2"></i>Referral Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Patient:</strong><br>
                            {{ $referralPatient ? $referralPatient->first_name . ' ' . $referralPatient->last_name : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Referred To:</strong><br>
                            {{ $referralDoctor ? $referralDoctor->first_name . ' ' . $referralDoctor->last_name : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Clinic:</strong><br>
                            {{ $referralClinic ? $referralClinic->name : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Referral Date:</strong><br>
                            {{ $referral->referral_date->format('Y-m-d') }}
                        </div>
                    </div>
                    @if($referral->reason)
                    <div class="row mt-3">
                        <div class="col-12"><strong>Reason:</strong><br>{{ $referral->reason }}</div>
                    </div>
                    @endif
                    @if($referral->notes)
                    <div class="row mt-2">
                        <div class="col-12"><strong>Notes:</strong><br>{{ $referral->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Booking form --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ph ph-calendar-check me-2"></i>Book Appointment</h5>
                    <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-secondary btn-sm">
                        <i class="ph ph-arrow-left me-1"></i>Back to Referral
                    </a>
                </div>
                <div class="card-body">
                    <form id="clinic-appointment-form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="referral_id" value="{{ $referral->id }}">
                        <input type="hidden" name="user_id" value="{{ $referralPatient->id }}">
                        <input type="hidden" name="patient_id" value="{{ $referralPatient->id }}">
                        <input type="hidden" name="clinic_id" value="{{ $referralClinic->id }}">
                        <input type="hidden" name="doctor_id" value="{{ $referralDoctor->id }}">

                        {{-- Patient card (locked) --}}
                        <div class="mb-3">
                            <label class="form-label">{{ __('appointment.lbl_select_patient') }} <span class="text-danger">*</span></label>
                            <div class="d-flex p-3 align-items-center gap-3 border rounded bg-body-secondary">
                                <img src="{{ $referralPatient->profile_image ?? default_user_avatar() }}"
                                    class="rounded-circle border object-fit-cover" width="56" height="56" alt="avatar">
                                <div>
                                    <h6 class="mb-1 fw-semibold">{{ $referralPatient->first_name . ' ' . $referralPatient->last_name }}</h6>
                                    <small class="text-muted d-block">{{ __('appointment.lbl_phone') }}: {{ $referralPatient->mobile ?? '' }}</small>
                                    <small class="text-muted d-block">{{ __('appointment.lbl_email') }}: {{ $referralPatient->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            {{-- Clinic (locked) --}}
                            <div class="col-md-6">
                                <label class="form-label">{{ __('appointment.lbl_select_clinic') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-body-secondary" value="{{ $referralClinic->name }}" readonly>
                            </div>

                            {{-- Doctor (locked) --}}
                            <div class="col-md-6">
                                <label class="form-label">{{ __('appointment.lbl_select_doctor') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-body-secondary"
                                    value="{{ $referralDoctor->first_name . ' ' . $referralDoctor->last_name }}" readonly>
                            </div>

                            {{-- Service --}}
                            <div class="col-md-6">
                                <label class="form-label">{{ __('appointment.lbl_select_service') }} <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="service-select" class="form-select" name="service_id">
                                        <option value=""></option>
                                    </select>
                                    <span id="service-loader" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                                        <i class="fas fa-spinner fa-spin text-primary"></i>
                                    </span>
                                </div>
                            </div>

                            {{-- Appointment Date --}}
                            <div class="col-md-6">
                                <label class="form-label">{{ __('appointment.lbl_appointment_date') }} <span class="text-danger">*</span></label>
                                <input type="text" id="appointment-date" name="appointment_date" class="form-control"
                                    placeholder="{{ __('appointment.lbl_appointment_date') }}"
                                    value="{{ $referral->referral_date->format('Y-m-d') }}">
                            </div>

                            {{-- Available Slots --}}
                            <div class="col-12">
                                <label class="form-label">{{ __('appointment.lbl_availble_slots') }} <span class="text-danger">*</span></label>
                                <div id="available-slots">
                                    <p class="text-muted text-center bg-body-secondary p-3 rounded">{{ __('appointment.lbl_slot_not_found') }}</p>
                                </div>
                            </div>

                            {{-- Medical Report --}}
                            <div class="col-12">
                                <label class="form-label">{{ __('appointment.lbl_medical_report') }}</label>
                                <input type="file" class="form-control" name="file_url[]" multiple accept=".jpeg,.jpg,.png,.gif,.pdf">
                            </div>

                            {{-- Medical History --}}
                            <div class="col-12">
                                <label class="form-label">{{ __('appointment.lbl_medical_history') }}</label>
                                <textarea class="form-control" name="appointment_extra_info" rows="3"
                                    placeholder="{{ __('appointment.lbl_medical_history_placeholder') }}">{{ $referral->notes }}</textarea>
                            </div>
                        </div>

                        {{-- Pricing --}}
                        <div class="mt-4 bg-body-secondary p-3 rounded border">
                            <div class="d-flex justify-content-between mb-2">
                                <span id="service-price-label">{{ __('appointment.lbl_service_price') }}:</span>
                                <span class="text-primary fw-bold" id="service-price">{{ $currencySymbol }}0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 d-none" id="discount-row">
                                <span id="discount-label">{{ __('appointment.lbl_discount') }}:</span>
                                <span class="text-success" id="discount-amount">-{{ $currencySymbol }}0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 d-none" id="subtotal-row">
                                <span>{{ __('appointment.lbl_subtotal') }}:</span>
                                <span id="subtotal-amount">{{ $currencySymbol }}0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    {{ __('appointment.lbl_tax') }}
                                    <span class="cursor-pointer ms-1" data-bs-toggle="collapse" data-bs-target="#applied-tax">
                                        <i class="ph ph-caret-down" id="tax-caret-icon"></i>
                                    </span>
                                </span>
                                <span class="text-danger" id="tax-inline-amount">{{ $currencySymbol }}0.00</span>
                            </div>
                            <div id="applied-tax" class="collapse mb-2 ps-2">
                                <div id="applied-tax-inline" class="text-muted small">{{ __('appointment.lbl_no_taxes_applied') }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>{{ __('appointment.lbl_total_amount') }}:</span>
                                <span class="text-success" id="total-amount">{{ $currencySymbol }}0.00</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-white">
                                {{ __('appointment.lbl_close') }}
                            </a>
                            <button type="submit" class="btn btn-secondary" id="save-appointment-btn">
                                <span class="save-btn-text">{{ __('appointment.lbl_save') }}</span>
                                <span class="spinner-border spinner-border-sm d-none" id="save-btn-spinner" role="status"></span>
                                <span class="loading-text d-none">{{ __('appointment.lbl_loading') }}...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var csrfToken      = '{{ csrf_token() }}';
    var currencySymbol = '{{ $currencySymbol }}';
    var clinicId       = {{ $referralClinic->id }};
    var doctorId       = {{ $referralDoctor->id }};
    var patientId      = {{ $referralPatient->id }};
    var redirectUrl    = '{{ route("backend.patientreferral.show", $referral) }}';

    var routes = {
        serviceList:            '{{ route("backend.appointments.services.index_list") }}',
        availableSlots:         '{{ route("backend.appointments.doctor.availableSlot") }}',
        servicePrice:           '{{ route("backend.appointments.services.service_price") }}',
        taxList:                '{{ route("backend.appointments.tax.index_list") }}',
        appointmentStore:       '{{ route("backend.appointment.store") }}',
        appointmentSavePayment: '{{ route("backend.appointment.save_payment") }}'
    };

    function fmt(n) {
        return currencySymbol + parseFloat(n || 0).toFixed(2);
    }

    $('#service-select').select2({ width: '100%', placeholder: '{{ __("appointment.lbl_select_service") }}' });

    function loadServices() {
        $('#service-loader').removeClass('d-none');
        $('#service-select').prop('disabled', true).empty().append('<option value=""></option>');
        $.getJSON(routes.serviceList, { doctor_id: doctorId, clinic_id: clinicId }, function (data) {
            $.each(data, function (i, s) {
                $('#service-select').append(new Option(s.name, s.id));
            });
            $('#service-select').trigger('change.select2');
        }).always(function () {
            $('#service-loader').addClass('d-none');
            $('#service-select').prop('disabled', false);
        });
    }

    function loadSlots() {
        var date      = $('#appointment-date').val();
        var serviceId = $('#service-select').val();
        if (!date || !serviceId) return;
        $('#available-slots').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin text-primary me-2"></i>{{ __("appointment.lbl_loading_available_slots") }}</div>');
        $.getJSON(routes.availableSlots, { appointment_date: date, doctor_id: doctorId, clinic_id: clinicId, service_id: serviceId }, function (r) {
            var slots = r.availableSlot || r.data || [];
            if (!slots.length) {
                $('#available-slots').html('<p class="text-muted text-center bg-body-secondary p-3 rounded">{{ __("appointment.lbl_slot_not_found") }}</p>');
                return;
            }
            var html = '<div class="d-flex flex-wrap gap-3 align-items-center">';
            $.each(slots, function (i, s) {
                html += '<input type="radio" class="btn-check form-check-input" id="slot' + i + '" name="appointment_time" value="' + s + '">';
                html += '<label for="slot' + i + '" class="clickable-text form-check-label">' + s + '</label>';
            });
            html += '</div>';
            $('#available-slots').html(html);
        }).fail(function () {
            $('#available-slots').html('<p class="text-muted text-center bg-body-secondary p-3 rounded">{{ __("appointment.lbl_slot_not_found") }}</p>');
        });
    }

    $(document).on('change', 'input[name="appointment_time"]', function () {
        $('label.form-check-label').removeClass('selected_slot');
        $(this).next('label').addClass('selected_slot');
    });

    $('#service-select').on('change', function () {
        var serviceId = $(this).val();
        loadSlots();
        if (!serviceId) return;
        $.getJSON(routes.servicePrice, { service_id: serviceId, doctor_id: doctorId }, function (r) {
            var base     = parseFloat(r.base_price || 0);
            var disc     = parseFloat(r.discount || 0);
            var subtotal = base - disc;
            $('#service-price').text(fmt(base));
            if (disc > 0) {
                $('#discount-row').removeClass('d-none');
                $('#discount-amount').text('-' + fmt(disc));
                $('#subtotal-row').removeClass('d-none');
                $('#subtotal-amount').text(fmt(subtotal));
            } else {
                $('#discount-row, #subtotal-row').addClass('d-none');
            }
            $.getJSON(routes.taxList, { service_id: serviceId, doctor_id: doctorId, clinic_id: clinicId, subtotal: subtotal, tax_type: r.tax_type || '' }, function (taxRes) {
                var totalTax = 0, taxHtml = '';
                if (Array.isArray(taxRes) && taxRes.length) {
                    $.each(taxRes, function (i, t) {
                        if (t.tax_type === 'exclusive' && t.status == 1) {
                            var tv = t.type === 'percent' ? (subtotal * t.value / 100) : parseFloat(t.value);
                            totalTax += tv;
                            taxHtml += '<div class="d-flex justify-content-between py-1 border-bottom"><span>' + (t.title || 'Tax') + ' (' + t.value + (t.type === 'percent' ? '%' : '') + ')</span><span class="fw-bold">' + fmt(tv) + '</span></div>';
                        }
                    });
                }
                $('#applied-tax-inline').html(taxHtml || '<span class="text-muted small">{{ __("appointment.lbl_no_taxes_applied") }}</span>');
                $('#tax-inline-amount').text(fmt(totalTax));
                $('#total-amount').text(fmt(subtotal + totalTax));
            });
        });
    });

    $('#appointment-date').on('change', loadSlots);

    $('#applied-tax').on('show.bs.collapse', function () {
        $('#tax-caret-icon').removeClass('ph-caret-down').addClass('ph-caret-up');
    }).on('hide.bs.collapse', function () {
        $('#tax-caret-icon').removeClass('ph-caret-up').addClass('ph-caret-down');
    });

    flatpickr('#appointment-date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'Y-m-d', minDate: 'today' });

    $('#clinic-appointment-form').on('submit', function (e) {
        e.preventDefault();
        var slot      = $('input[name="appointment_time"]:checked').val();
        var serviceId = $('#service-select').val();
        var date      = $('#appointment-date').val();
        var hasErrors = false;
        $('.field-error').remove();
        if (!serviceId) {
            $('#service-select').closest('.position-relative').append('<div class="field-error text-danger small mt-1">{{ __("appointment.lbl_service_required") }}</div>');
            hasErrors = true;
        }
        if (!date) {
            $('#appointment-date').parent().append('<div class="field-error text-danger small mt-1">{{ __("appointment.lbl_appointment_date_required") }}</div>');
            hasErrors = true;
        }
        if (!slot) {
            $('#available-slots').parent().append('<div class="field-error text-danger small mt-1">{{ __("appointment.lbl_time_slot_required") }}</div>');
            hasErrors = true;
        }
        if (hasErrors) return;

        var fd = new FormData(this);
        fd.set('appointment_time', slot);

        $('#save-appointment-btn .save-btn-text').addClass('d-none');
        $('#save-btn-spinner').removeClass('d-none');
        $('#save-appointment-btn .loading-text').removeClass('d-none');
        $('#save-appointment-btn').prop('disabled', true);

        $.ajax({
            url: routes.appointmentStore, method: 'POST', data: fd,
            processData: false, contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken }
        }).done(function (res) {
            if (res.status) {
                $.ajax({
                    url: routes.appointmentSavePayment, method: 'POST',
                    data: res.data, headers: { 'X-CSRF-TOKEN': csrfToken }
                }).always(function () { window.location.href = redirectUrl; });
            } else {
                alert(res.message || '{{ __("appointment.lbl_server_error") }}');
                resetBtn();
            }
        }).fail(function () {
            alert('{{ __("appointment.lbl_server_error") }}');
            resetBtn();
        });
    });

    function resetBtn() {
        $('#save-appointment-btn .save-btn-text').removeClass('d-none');
        $('#save-btn-spinner').addClass('d-none');
        $('#save-appointment-btn .loading-text').addClass('d-none');
        $('#save-appointment-btn').prop('disabled', false);
    }

    loadServices();
});
</script>
@endpush
