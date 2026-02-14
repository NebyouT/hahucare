@extends('backend.layouts.app')

@section('title', 'Create Lab Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create Lab Order</h4>
                </div>
                <div class="card-body">
                    <form id="lab-order-form" method="POST" action="{{ route('backend.lab-orders.store') }}">
                        @csrf
                        
                        <!-- Step 1: Clinic Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-hospital"></i> Step 1: Select Clinic</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                                    <select class="form-select" id="clinic_id" name="clinic_id" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Lab Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-flask"></i> Step 2: Select Lab in this Clinic</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="lab_id" class="form-label">Lab <span class="text-danger">*</span></label>
                                    <select class="form-select" id="lab_id" name="lab_id" required disabled>
                                        <option value="">Select Lab</option>
                                    </select>
                                    <small class="text-muted">Please select a clinic first</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Service Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-vial"></i> Step 3: Select Services in this Lab</h5>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">Services <span class="text-danger">*</span></label>
                                    <div id="services-container">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Please select a lab first to see available services
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Doctor Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-user-md"></i> Step 4: Select Doctor in this Clinic</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="doctor_id" name="doctor_id" required disabled>
                                        <option value="">Select Doctor</option>
                                    </select>
                                    <small class="text-muted">Please select a clinic first</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Patient Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-user-patient"></i> Step 5: Select Patient</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-select" id="patient_id" name="patient_id" required disabled>
                                        <option value="">Select Patient</option>
                                    </select>
                                    <small class="text-muted">Please select a doctor first</small>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Hospital Fields -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-notes-medical"></i> Additional Information</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="order_type" class="form-label">Order Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="order_type" name="order_type" required>
                                        <option value="">Select Order Type</option>
                                        <option value="outpatient">Outpatient</option>
                                        <option value="inpatient">Inpatient</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="routine">Routine</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="stat">STAT</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="clinical_indication" class="form-label">Clinical Indication</label>
                                    <textarea class="form-control" id="clinical_indication" name="clinical_indication" rows="2" placeholder="Reason for the test..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="diagnosis_suspected" class="form-label">Suspected Diagnosis</label>
                                    <textarea class="form-control" id="diagnosis_suspected" name="diagnosis_suspected" rows="2" placeholder="Suspected diagnosis..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="collection_type" class="form-label">Collection Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="collection_type" name="collection_type" required>
                                        <option value="">Select Collection Type</option>
                                        <option value="venipuncture">Venipuncture</option>
                                        <option value="urine">Urine</option>
                                        <option value="swab">Swab</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="encounter_id" class="form-label">Encounter ID</label>
                                    <input type="number" class="form-control" id="encounter_id" name="encounter_id" placeholder="Optional">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" placeholder="Hospital department">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="ward_room" class="form-label">Ward/Room</label>
                                    <input type="text" class="form-control" id="ward_room" name="ward_room" placeholder="For inpatients">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="collection_notes" class="form-label">Collection Notes</label>
                                    <textarea class="form-control" id="collection_notes" name="collection_notes" rows="2" placeholder="Special collection instructions..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('backend.lab-orders.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Lab Orders
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                        <i class="fas fa-save"></i> Create Lab Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let selectedServices = [];
    
    // Clinic selection triggers lab and doctor loading
    $('#clinic_id').on('change', function() {
        const clinicId = $(this).val();
        
        console.log('Clinic selected:', clinicId); // Debug log
        
        // Reset dependent fields
        $('#lab_id').html('<option value="">Select Lab</option>').prop('disabled', !clinicId);
        $('#doctor_id').html('<option value="">Select Doctor</option>').prop('disabled', !clinicId);
        $('#patient_id').html('<option value="">Select Patient</option>').prop('disabled', true);
        $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select a lab first to see available services</div>');
        selectedServices = [];
        updateSubmitButton();
        
        if (clinicId) {
            // Load labs
            console.log('Loading labs for clinic:', clinicId); // Debug log
            $.get(`/app/lab-orders/get-labs-by-clinic/${clinicId}`, function(data) {
                console.log('Labs received:', data); // Debug log
                data.forEach(function(lab) {
                    $('#lab_id').append(`<option value="${lab.id}">${lab.name}</option>`);
                });
                if (data.length === 0) {
                    $('#lab_id').after('<small class="text-danger">No labs found for this clinic</small>');
                }
            }).fail(function(xhr) {
                console.error('Error loading labs:', xhr.responseText); // Debug log
                $('#lab_id').after('<small class="text-danger">Error loading labs</small>');
            });
            
            // Load doctors
            $.get(`/app/lab-orders/get-doctors-by-clinic/${clinicId}`, function(data) {
                console.log('Doctors received:', data); // Debug log
                data.forEach(function(doctor) {
                    $('#doctor_id').append(`<option value="${doctor.id}">${doctor.first_name} ${doctor.last_name}</option>`);
                });
            });
        }
    });
    
    // Lab selection triggers services loading
    $('#lab_id').on('change', function() {
        const labId = $(this).val();
        
        $('#services-container').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Loading services...</div>');
        selectedServices = [];
        updateSubmitButton();
        
        if (labId) {
            $.get(`/app/lab-orders/get-services-by-lab/${labId}`, function(data) {
                if (data.length === 0) {
                    $('#services-container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No services available for this lab</div>');
                } else {
                    let html = '<div class="row">';
                    data.forEach(function(service) {
                        html += `
                            <div class="col-md-4 mb-3">
                                <div class="card service-card" data-service-id="${service.id}" data-service-name="${service.name}" data-service-price="${service.price}">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input service-checkbox" type="checkbox" value="${service.id}" id="service_${service.id}">
                                            <label class="form-check-label" for="service_${service.id}">
                                                <strong>${service.name}</strong><br>
                                                <small class="text-muted">${service.description || 'No description'}</small><br>
                                                <span class="badge bg-primary">$${service.price}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#services-container').html(html);
                }
            });
        } else {
            $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select a lab first to see available services</div>');
        }
    });
    
    // Service selection handling
    $(document).on('change', '.service-checkbox', function() {
        const serviceId = $(this).val();
        const card = $(this).closest('.service-card');
        
        if ($(this).is(':checked')) {
            selectedServices.push(serviceId);
            card.addClass('border-primary');
        } else {
            selectedServices = selectedServices.filter(id => id !== serviceId);
            card.removeClass('border-primary');
        }
        
        updateSubmitButton();
    });
    
    // Doctor selection triggers patient loading
    $('#doctor_id').on('change', function() {
        const doctorId = $(this).val();
        
        $('#patient_id').html('<option value="">Select Patient</option>').prop('disabled', !doctorId);
        updateSubmitButton();
        
        if (doctorId) {
            $.get(`/app/lab-orders/get-patients-by-doctor/${doctorId}`, function(data) {
                data.forEach(function(patient) {
                    $('#patient_id').append(`<option value="${patient.id}">${patient.first_name} ${patient.last_name}</option>`);
                });
            });
        }
    });
    
    // Patient selection
    $('#patient_id').on('change', updateSubmitButton);
    
    // Order type and priority selection
    $('#order_type, #priority, #collection_type').on('change', updateSubmitButton);
    
    function updateSubmitButton() {
        const clinicId = $('#clinic_id').val();
        const labId = $('#lab_id').val();
        const doctorId = $('#doctor_id').val();
        const patientId = $('#patient_id').val();
        const orderType = $('#order_type').val();
        const priority = $('#priority').val();
        const collectionType = $('#collection_type').val();
        
        const canSubmit = clinicId && labId && doctorId && patientId && 
                         selectedServices.length > 0 && orderType && priority && collectionType;
        
        $('#submit-btn').prop('disabled', !canSubmit);
    }
    
    // Form submission
    $('#lab-order-form').on('submit', function(e) {
        e.preventDefault();
        
        // Prepare services data
        const servicesData = [];
        $('.service-checkbox:checked').each(function() {
            const serviceId = $(this).val();
            const card = $(this).closest('.service-card');
            servicesData.push({
                lab_service_id: serviceId,
                urgent_flag: false,
                clinical_notes: '',
                sample_type: '',
                fasting_required: false,
                special_instructions: ''
            });
        });
        
        // Add services data to form
        const formData = new FormData(this);
        formData.delete('services'); // Remove any existing services data
        servicesData.forEach((service, index) => {
            Object.keys(service).forEach(key => {
                formData.append(`services[${index}][${key}]`, service[key]);
            });
        });
        
        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                window.location.href = '/app/lab-orders';
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message || 'Something went wrong');
            }
        });
    });
});
</script>
@endsection
