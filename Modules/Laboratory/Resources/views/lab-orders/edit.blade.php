@extends('backend.layouts.app')

@section('title', 'Edit Lab Order #' . $labOrder->order_number)

@section('content')
@php
    $existingServiceIds = $labOrder->labOrderItems->pluck('lab_service_id')->toArray();
@endphp

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Lab Order <span class="text-muted">#{{ $labOrder->order_number }}</span></h5>
                    <a href="{{ route('backend.lab-orders.show', $labOrder) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="lab-order-form" method="POST" action="{{ route('backend.lab-orders.update', $labOrder) }}">
                        @csrf
                        @method('PUT')

                        {{-- Pass through fields not shown in this form so they are preserved --}}
                        <input type="hidden" name="order_type"    value="{{ $labOrder->order_type }}">
                        <input type="hidden" name="priority"      value="{{ $labOrder->priority }}">
                        <input type="hidden" name="collection_type" value="{{ $labOrder->collection_type }}">
                        <input type="hidden" name="status"        value="{{ $labOrder->status }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="clinic_id" class="form-label fw-semibold">Clinic <span class="text-danger">*</span></label>
                                <select class="form-select" id="clinic_id" name="clinic_id" required>
                                    <option value="">Select Clinic</option>
                                    @foreach($clinics as $clinic)
                                        <option value="{{ $clinic->id }}" {{ $labOrder->clinic_id == $clinic->id ? 'selected' : '' }}>
                                            {{ $clinic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="lab_id" class="form-label fw-semibold">Lab <span class="text-danger">*</span></label>
                                <select class="form-select" id="lab_id" name="lab_id" required>
                                    <option value="">Select Lab</option>
                                    @foreach($labs as $lab)
                                        <option value="{{ $lab->id }}" {{ $labOrder->lab_id == $lab->id ? 'selected' : '' }}>
                                            {{ $lab->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="doctor_id" class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ $labOrder->doctor_id == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->first_name }} {{ $doctor->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ $labOrder->patient_id == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-vial me-1"></i> Services</label>
                            <div id="services-container">
                                @if($labServices->isEmpty())
                                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i> No services found for this lab.</div>
                                @else
                                    <div class="row">
                                        @foreach($labServices as $service)
                                            @php $isChecked = in_array($service->id, $existingServiceIds); @endphp
                                            <div class="col-md-4 mb-3">
                                                <div class="card service-card {{ $isChecked ? 'border-primary' : '' }}" data-service-id="{{ $service->id }}">
                                                    <div class="card-body py-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input service-checkbox" type="checkbox"
                                                                   value="{{ $service->id }}" id="service_{{ $service->id }}"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="service_{{ $service->id }}">
                                                                <strong>{{ $service->name }}</strong><br>
                                                                <small class="text-muted">{{ $service->description ?? '' }}</small><br>
                                                                <span class="badge bg-primary">{{ $service->price }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backend.lab-orders.show', $labOrder) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    function loadServices(labId) {
        if (!labId) {
            $('#services-container').html('<div class="alert alert-info">Please select a lab to see available services.</div>');
            return;
        }
        $('#services-container').html('<div class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Loading services...</div>');
        $.get('/app/lab-orders/get-services-by-lab/' + labId, function(data) {
            if (!data.length) {
                $('#services-container').html('<div class="alert alert-warning">No services available for this lab.</div>');
                return;
            }
            let html = '<div class="row">';
            data.forEach(function(service) {
                html += '<div class="col-md-4 mb-3">'
                    + '<div class="card service-card" data-service-id="' + service.id + '">'
                    + '<div class="card-body py-2"><div class="form-check">'
                    + '<input class="form-check-input service-checkbox" type="checkbox" value="' + service.id + '" id="svc_' + service.id + '">'
                    + '<label class="form-check-label" for="svc_' + service.id + '">'
                    + '<strong>' + service.name + '</strong><br>'
                    + '<small class="text-muted">' + (service.description || '') + '</small><br>'
                    + '<span class="badge bg-primary">' + service.price + '</span>'
                    + '</label></div></div></div></div>';
            });
            html += '</div>';
            $('#services-container').html(html);
        }).fail(function() {
            $('#services-container').html('<div class="alert alert-danger">Error loading services.</div>');
        });
    }

    // Only reload via AJAX when lab selection changes
    $('#lab_id').on('change', function() {
        loadServices($(this).val());
    });

    // Service checkbox border toggle
    $(document).on('change', '.service-checkbox', function() {
        const card = $(this).closest('.service-card');
        if ($(this).is(':checked')) {
            card.addClass('border-primary');
        } else {
            card.removeClass('border-primary');
        }
    });

    // Form submission
    $('#lab-order-form').on('submit', function(e) {
        e.preventDefault();

        const checkedServices = [];
        $('.service-checkbox:checked').each(function() {
            checkedServices.push({
                lab_service_id: $(this).val(),
                urgent_flag: false,
                clinical_notes: '',
                sample_type: '',
                fasting_required: false,
                special_instructions: ''
            });
        });

        const formData = new FormData(this);
        checkedServices.forEach(function(service, index) {
            Object.keys(service).forEach(function(key) {
                formData.append(`services[${index}][${key}]`, service[key]);
            });
        });

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                window.location.href = '{{ route("backend.lab-orders.index") }}';
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong';
                alert('Error: ' + msg);
            }
        });
    });
});
</script>
@endsection
