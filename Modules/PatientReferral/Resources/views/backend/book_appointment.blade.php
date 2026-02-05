<!-- Modules/PatientReferral/Resources/views/backend/book_appointment.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Book Appointment from Referral')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.patientreferral.index') }}">Referrals</a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.patientreferral.show', $referral) }}">Referral #{{ $referral->id }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Book Appointment</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Referral Appointment Booking</h1>
            
            <!-- Referral Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md"></i> Referral Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Patient:</strong><br>
                            {{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Referred To:</strong><br>
                            {{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Referral Date:</strong><br>
                            {{ $referral->referral_date->format('Y-m-d') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-{{ $referral->status === 'accepted' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($referral->status) }}
                            </span>
                        </div>
                    </div>
                    @if($referral->reason)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Reason for Referral:</strong><br>
                            {{ $referral->reason }}
                        </div>
                    </div>
                    @endif
                    @if($referral->notes)
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Additional Notes:</strong><br>
                            {{ $referral->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Appointment Booking Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check"></i> Book Appointment
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Referral
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="referral-appointment-form" method="POST" action="{{ route('backend.appointments.store') }}">
                        @csrf
                        
                        <!-- Hidden referral data -->
                        <input type="hidden" name="user_id" value="{{ $referral->patient_id }}">
                        <input type="hidden" name="doctor_id" value="{{ $referral->referred_to }}">
                        <input type="hidden" name="referral_id" value="{{ $referral->id }}">
                        <input type="hidden" name="from_referral" value="true">
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">{{ __('clinic.lbl_select_patient') }} <span class="text-danger">*</span></label>
                                    <div class="form-control bg-light">
                                        {{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}
                                        <small class="text-muted">(Pre-filled from referral)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('clinic.lbl_select_clinic') }} <span class="text-danger">*</span></label>
                                    @if($clinics->count() > 0)
                                        <select name="clinic_id" id="clinic_id" class="form-control" required>
                                            <option value="">Select Clinic</option>
                                            @foreach($clinics as $clinic)
                                                <option value="{{ $clinic->id }}" 
                                                        {{ $doctorClinic && $doctorClinic->id == $clinic->id ? 'selected' : '' }}>
                                                    {{ $clinic->name }}
                                                    @if($clinic->address)
                                                        - {{ $clinic->address }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="alert alert-warning">
                                            No clinics available. Please contact administrator.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('clinic.lbl_select_doctor') }} <span class="text-danger">*</span></label>
                                    <div class="form-control bg-light">
                                        {{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}
                                        <small class="text-muted">(Pre-filled from referral)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('clinic.lbl_select_service') }} <span class="text-danger">*</span></label>
                                    <select name="service_id" id="service_id" class="form-control" required>
                                        <option value="">Select Service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                    data-price="{{ $service->price }}">
                                                {{ $service->name }} - ${{ number_format($service->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ __('clinic.lbl_appointment_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                                           min="{{ date('Y-m-d') }}" 
                                           value="{{ old('appointment_date', date('Y-m-d')) }}" required />
                                    <small class="form-text text-muted">Must be today or later</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Appointment Time <span class="text-danger">*</span></label>
                                    <select name="appointment_time" id="appointment_time" class="form-control" required>
                                        <option value="">Select Time</option>
                                        @php
                                            $startHour = 9;
                                            $endHour = 17;
                                            $interval = 30; // minutes
                                        @endphp
                                        
                                        @for($hour = $startHour; $hour < $endHour; $hour++)
                                            @for($minute = 0; $minute < 60; $minute += $interval)
                                                @php
                                                    $time = sprintf('%02d:%02d:00', $hour, $minute);
                                                    $displayTime = date('h:i A', strtotime($time));
                                                @endphp
                                                <option value="{{ $time }}">{{ $displayTime }}</option>
                                            @endfor
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Appointment Type <span class="text-danger">*</span></label>
                                    <select name="appointment_type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="consultation">Consultation</option>
                                        <option value="followup">Follow-up</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="routine">Routine Checkup</option>
                                        <option value="specialist">Specialist Visit</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="insurance">Insurance</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="appointment_extra_info">Additional Notes for Appointment</label>
                                    <textarea name="appointment_extra_info" id="appointment_extra_info" class="form-control" rows="3" 
                                              placeholder="Any additional information for the appointment...">{{ old('appointment_extra_info', $referral->notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="emergency_contact">Emergency Contact (Optional)</label>
                                    <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" 
                                           placeholder="Name and phone number of emergency contact"
                                           value="{{ old('emergency_contact') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Reminder Preferences -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">Reminder Preferences</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="send_sms_reminder" name="send_sms_reminder" checked>
                                                    <label class="form-check-label" for="send_sms_reminder">
                                                        Send SMS Reminder
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="send_email_reminder" name="send_email_reminder" checked>
                                                    <label class="form-check-label" for="send_email_reminder">
                                                        Send Email Reminder
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="send_push_reminder" name="send_push_reminder" checked>
                                                    <label class="form-check-label" for="send_push_reminder">
                                                        Send Push Notification
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estimated Cost Display -->
                        <div class="row mt-3" id="cost-display" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-dollar-sign"></i> Estimated Cost</h6>
                                    <p id="estimated-cost">Service cost: $0.00</p>
                                    <small class="text-muted">This is an estimate. Final cost may vary.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary" id="submit-btn">
                                            <i class="fas fa-calendar-plus"></i> Book Appointment
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="saveAsDraft()">
                                            <i class="fas fa-save"></i> Save as Draft
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="mt-3">Booking Appointment...</h5>
                <p>Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast Template -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto"><i class="fas fa-check-circle"></i> Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Appointment booked successfully!
        </div>
    </div>
</div>

<!-- Error Toast Template -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="fas fa-exclamation-circle"></i> Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="errorToastBody">
            An error occurred.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('referral-appointment-form');
    const submitBtn = document.getElementById('submit-btn');
    const serviceSelect = document.getElementById('service_id');
    const costDisplay = document.getElementById('cost-display');
    const estimatedCost = document.getElementById('estimated-cost');
    const appointmentDate = document.getElementById('appointment_date');
    const clinicSelect = document.getElementById('clinic_id');
    
    // Initialize Bootstrap toasts
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));
    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
    
    // Show estimated cost when service is selected
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        
        if (price) {
            costDisplay.style.display = 'block';
            estimatedCost.textContent = `Service cost: $${parseFloat(price).toFixed(2)}`;
        } else {
            costDisplay.style.display = 'none';
        }
    });
    
    // Check availability when date or clinic changes
    let availabilityTimeout;
    appointmentDate.addEventListener('change', checkAvailability);
    clinicSelect.addEventListener('change', checkAvailability);
    
    function checkAvailability() {
        const date = appointmentDate.value;
        const clinicId = clinicSelect.value;
        
        if (!date || !clinicId) return;
        
        // Clear previous timeout
        clearTimeout(availabilityTimeout);
        
        // Set new timeout to avoid too many requests
        availabilityTimeout = setTimeout(() => {
            fetchAvailability(clinicId, date);
        }, 500);
    }
    
    function fetchAvailability(clinicId, date) {
        fetch(`{{ url('api/check-availability') }}?clinic_id=${clinicId}&date=${date}&doctor_id={{ $referral->referred_to }}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.available === false) {
                    showWarning('Selected clinic/doctor is not available on the chosen date. Please select another date.');
                }
            })
            .catch(error => {
                console.error('Availability check error:', error);
                // Silently fail - don't show error to user for this optional feature
            });
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Prepare form data
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        
        // Add additional fields
        payload.status = 'confirmed';
        
        // AJAX request
        fetch(form.action, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            loadingModal.hide();
            
            if (data.success || data.status) {
                // Show success message
                successToast.show();
                
                // Redirect after delay
                setTimeout(() => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = '{{ route("backend.patientreferral.show", $referral) }}';
                    }
                }, 2000);
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            loadingModal.hide();
            console.error('Error:', error);
            
            // Show error message
            document.getElementById('errorToastBody').textContent = 'Error booking appointment: ' + error.message;
            errorToast.show();
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-calendar-plus"></i> Book Appointment';
        });
    });
    
    function validateForm() {
        // Clear previous errors
        clearErrors();
        
        let isValid = true;
        const today = new Date().toISOString().split('T')[0];
        
        // Check appointment date
        const appointmentDateValue = appointmentDate.value;
        if (!appointmentDateValue) {
            showError(appointmentDate, 'Appointment date is required');
            isValid = false;
        } else if (appointmentDateValue < today) {
            showError(appointmentDate, 'Appointment date cannot be in the past');
            isValid = false;
        }
        
        // Check appointment time
        const appointmentTime = document.getElementById('appointment_time');
        if (!appointmentTime.value) {
            showError(appointmentTime, 'Appointment time is required');
            isValid = false;
        }
        
        // Check service
        if (!serviceSelect.value) {
            showError(serviceSelect, 'Please select a service');
            isValid = false;
        }
        
        // Check appointment type
        const appointmentType = document.querySelector('select[name="appointment_type"]');
        if (!appointmentType.value) {
            showError(appointmentType, 'Please select appointment type');
            isValid = false;
        }
        
        // Check payment method
        const paymentMethod = document.querySelector('select[name="payment_method"]');
        if (!paymentMethod.value) {
            showError(paymentMethod, 'Please select payment method');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showError(element, message) {
        element.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        
        element.parentNode.appendChild(errorDiv);
    }
    
    function clearErrors() {
        const errorElements = document.querySelectorAll('.is-invalid, .invalid-feedback');
        errorElements.forEach(element => {
            if (element.classList.contains('is-invalid')) {
                element.classList.remove('is-invalid');
            } else {
                element.remove();
            }
        });
    }
    
    function showWarning(message) {
        const warningDiv = document.createElement('div');
        warningDiv.className = 'alert alert-warning mt-2';
        warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        
        // Add after clinic select
        clinicSelect.parentNode.appendChild(warningDiv);
        
        // Remove after 5 seconds
        setTimeout(() => {
            warningDiv.remove();
        }, 5000);
    }
});

// Save as draft function
function saveAsDraft() {
    const form = document.getElementById('referral-appointment-form');
    const formData = new FormData(form);
    
    // Change status to draft
    formData.append('status', 'draft');
    
    // You can implement draft saving logic here
    // For now, just show a message
    alert('Draft saving functionality to be implemented');
}

// Initialize date picker with restrictions
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('appointment_date').setAttribute('min', today);
    
    // Set default time to next available slot
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    
    const timeSelect = document.getElementById('appointment_time');
    if (timeSelect) {
        // Find next available time slot
        let foundTime = false;
        for (let option of timeSelect.options) {
            if (option.value) {
                const [hours, minutes] = option.value.split(':').map(Number);
                if (hours > currentHour || (hours === currentHour && minutes > currentMinute)) {
                    option.selected = true;
                    foundTime = true;
                    break;
                }
            }
        }
        
        // If no future time today, select first option tomorrow
        if (!foundTime) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('appointment_date').value = tomorrow.toISOString().split('T')[0];
            timeSelect.options[1].selected = true; // Select first time slot
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.card-header.bg-primary {
    color: white;
}

.toast {
    min-width: 300px;
}
</style>
@endpush