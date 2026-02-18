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
                        
                        <!-- Basic Information Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
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

                        <!-- Services Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-vial"></i> Lab Services</h5>
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

                        <!-- Patient & Doctor Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-users"></i> Patient & Doctor</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @if(auth()->user()->hasRole('doctor'))
                                            <option value="{{ auth()->user()->id }}" selected>
                                                Dr. {{ auth()->user()->full_name }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-select" id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fas fa-notes-medical"></i> Additional Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="order_type" class="form-label">Order Type</label>
                                    <select class="form-select" id="order_type" name="order_type">
                                        <option value="outpatient">Outpatient</option>
                                        <option value="inpatient">Inpatient</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="routine">Routine</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="stat">Stat</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="form-label">Clinical Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                        placeholder="Add any relevant clinical information, symptoms, or special requirements..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Lab Order
                                </button>
                                <a href="{{ route('backend.lab-orders.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
$(document).ready(function() {
    console.log('Lab order form initialized'); // Debug log
    
    // Load all doctors and patients on page load
    loadDoctors();
    loadPatients();
    
    // Clinic selection triggers lab loading
    $('#clinic_id').on('change', function() {
        const clinicId = $(this).val();
        console.log('Clinic selected:', clinicId); // Debug log
        
        if (clinicId) {
            loadLabsForClinic(clinicId);
        } else {
            resetLabSelection();
        }
    });
    
    // Lab selection triggers services loading
    $('#lab_id').on('change', function() {
        const labId = $(this).val();
        console.log('Lab selected:', labId); // Debug log
        
        if (labId) {
            loadServicesForLab(labId);
        } else {
            resetServicesSelection();
        }
    });
});

function loadDoctors() {
    console.log('Loading doctors...'); // Debug log
    
    $.get('/app/lab-orders/get-all-doctors', function(data) {
        console.log('Doctors received:', data); // Debug log
        
        const doctorSelect = $('#doctor_id');
        doctorSelect.empty().append('<option value="">Select Doctor</option>');
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(function(doctor) {
                doctorSelect.append(`<option value="${doctor.id}">Dr. ${doctor.first_name} ${doctor.last_name}</option>`);
            });
        }
    }).fail(function(xhr) {
        console.error('Error loading doctors:', xhr.responseText);
    });
}

function loadPatients() {
    console.log('Loading patients...'); // Debug log
    
    $.get('/app/lab-orders/get-all-patients', function(data) {
        console.log('Patients received:', data); // Debug log
        
        const patientSelect = $('#patient_id');
        patientSelect.empty().append('<option value="">Select Patient</option>');
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(function(patient) {
                patientSelect.append(`<option value="${patient.id}">${patient.first_name} ${patient.last_name}</option>`);
            });
        }
    }).fail(function(xhr) {
        console.error('Error loading patients:', xhr.responseText);
    });
}

function loadLabsForClinic(clinicId) {
    console.log('Loading labs for clinic:', clinicId); // Debug log
    
    const labSelect = $('#lab_id');
    labSelect.empty().append('<option value="">Loading labs...</option>').prop('disabled', false);
    
    $.get(`/app/lab-orders/get-labs-by-clinic/${clinicId}`, function(data) {
        console.log('Labs received:', data); // Debug log
        
        labSelect.empty().append('<option value="">Select Lab</option>');
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(function(lab) {
                const label = lab.same_clinic
                    ? lab.name
                    : lab.name + (lab.clinic_name ? ' (' + lab.clinic_name + ')' : '');
                labSelect.append(`<option value="${lab.id}">${label}</option>`);
            });
        } else {
            labSelect.append('<option value="">No labs available</option>');
            labSelect.prop('disabled', true);
        }
    }).fail(function(xhr) {
        console.error('Error loading labs:', xhr.responseText);
        labSelect.empty().append('<option value="">Error loading labs</option>').prop('disabled', true);
    });
}

function loadServicesForLab(labId) {
    console.log('Loading services for lab:', labId); // Debug log
    
    const servicesContainer = $('#services-container');
    servicesContainer.html('<div class="text-center"><div class="spinner-border text-primary"></div> Loading services...</div>');
    
    $.get(`/app/lab-orders/get-services-by-lab/${labId}`, function(data) {
        console.log('Services received:', data); // Debug log
        displayServices(data);
    }).fail(function(xhr) {
        console.error('Error loading services:', xhr.responseText);
        servicesContainer.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error loading services</div>');
    });
}

function displayServices(services) {
    const servicesContainer = $('#services-container');
    
    if (!Array.isArray(services) || services.length === 0) {
        servicesContainer.html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No services available for this lab</div>');
        return;
    }
    
    let html = '<div class="row">';
    services.forEach(function(service) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox" 
                                id="service_${service.id}" value="${service.id}" name="services[]">
                            <label class="form-check-label" for="service_${service.id}">
                                <strong>${service.name}</strong>
                                ${service.price ? '<span class="badge bg-primary ms-2">$' + service.price + '</span>' : ''}
                            </label>
                        </div>
                        ${service.description ? '<p class="text-muted small mb-2">' + service.description + '</p>' : ''}
                        ${service.category_name ? '<span class="badge bg-light text-dark">' + service.category_name + '</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    servicesContainer.html(html);
}

function resetLabSelection() {
    $('#lab_id').empty().append('<option value="">Select Lab</option>').prop('disabled', true);
    resetServicesSelection();
}

function resetServicesSelection() {
    $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select a lab first to see available services</div>');
}

// Form validation before submission
$('#lab-order-form').on('submit', function(e) {
    const selectedServices = $('.service-checkbox:checked');
    
    if (selectedServices.length === 0) {
        e.preventDefault();
        alert('Please select at least one service');
        return false;
    }
    
    console.log('Form submitted with services:', selectedServices.length); // Debug log
    return true;
});
</script>
@endpush
@endsection
