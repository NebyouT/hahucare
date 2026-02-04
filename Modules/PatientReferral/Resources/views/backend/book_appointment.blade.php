<!-- Modules/PatientReferral/Resources/views/backend/book_appointment.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Book Appointment from Referral')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
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
                            <span class="badge badge-success">{{ ucfirst($referral->status) }}</span>
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

            <!-- Appointment Booking Card -->
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
                    <form id="referral-appointment-form" action="{{ route('backend.appointment.store') }}" method="POST">
                        @csrf
                        
                        <!-- Hidden referral data -->
                        <input type="hidden" name="user_id" value="{{ $referral->patient_id }}">
                        <input type="hidden" name="doctor_id" value="{{ $referral->referred_to }}">
                        <input type="hidden" name="referral_id" value="{{ $referral->id }}">
                        <input type="hidden" name="from_referral" value="true">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="clinic_id">Select Clinic <span class="text-danger">*</span></label>
                                    <select name="clinic_id" id="clinic_id" class="form-control" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" data-error-for="clinic_id"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_id">Select Service <span class="text-danger">*</span></label>
                                    <select name="service_id" id="service_id" class="form-control" required>
                                        <option value="">Select Service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" data-doctor-id="{{ $service->doctor_id }}">
                                                {{ $service->name }} - ${{ number_format($service->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" data-error-for="service_id"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_date">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                                           value="{{ $referral->referral_date->format('Y-m-d') }}" required>
                                    <span class="text-danger" data-error-for="appointment_date"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_time">Preferred Time <span class="text-danger">*</span></label>
                                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required>
                                    <span class="text-danger" data-error-for="appointment_time"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Available Slots Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Available Time Slots</label>
                                    <div id="available-slots" class="d-flex flex-wrap gap-2">
                                        <div class="text-muted">Select clinic and service to view available slots</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="appointment_extra_info">Additional Notes</label>
                                    <textarea name="appointment_extra_info" id="appointment_extra_info" class="form-control" rows="3" 
                                              placeholder="Any additional information for the appointment...">{{ $referral->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Service Price Display -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Appointment Details</h6>
                                    <div id="service-details">
                                        <p><strong>Service:</strong> <span id="selected-service-name">Not selected</span></p>
                                        <p><strong>Duration:</strong> <span id="selected-service-duration">-</span> minutes</p>
                                        <p><strong>Price:</strong> $<span id="selected-service-price">0.00</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="book-appointment-btn">
                                        <i class="fas fa-calendar-plus"></i> Book Appointment
                                    </button>
                                    <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
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
    // Service selection handler
    $('#service_id').change(function() {
        var serviceId = $(this).val();
        var selectedOption = $(this).find('option:selected');
        
        if (serviceId) {
            $('#selected-service-name').text(selectedOption.text().split(' - ')[0]);
            $('#selected-service-price').text(selectedOption.data('price') || '0.00');
            $('#selected-service-duration').text(selectedOption.data('duration') || '30');
            
            // Load available slots
            loadAvailableSlots();
        } else {
            $('#selected-service-name').text('Not selected');
            $('#selected-service-price').text('0.00');
            $('#selected-service-duration').text('-');
            $('#available-slots').html('<div class="text-muted">Select service to view available slots</div>');
        }
    });
    
    // Clinic selection handler
    $('#clinic_id').change(function() {
        if ($('#service_id').val()) {
            loadAvailableSlots();
        }
    });
    
    // Date change handler
    $('#appointment_date').change(function() {
        if ($('#service_id').val() && $('#clinic_id').val()) {
            loadAvailableSlots();
        }
    });
    
    function loadAvailableSlots() {
        var clinicId = $('#clinic_id').val();
        var serviceId = $('#service_id').val();
        var date = $('#appointment_date').val();
        var doctorId = {{ $referral->referred_to }};
        
        if (!clinicId || !serviceId || !date) {
            return;
        }
        
        $('#available-slots').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading available slots...</div>');
        
        $.ajax({
            url: '/api/doctor-available-slots',
            method: 'GET',
            data: {
                doctor_id: doctorId,
                clinic_id: clinicId,
                service_id: serviceId,
                date: date
            },
            success: function(response) {
                if (response.status && response.data.slots.length > 0) {
                    var slotsHtml = '';
                    response.data.slots.forEach(function(slot) {
                        slotsHtml += '<button type="button" class="btn btn-outline-primary time-slot-btn" data-time="' + slot.time + '">' + 
                                    slot.display_time + '</button>';
                    });
                    $('#available-slots').html(slotsHtml);
                    
                    // Time slot click handler
                    $('.time-slot-btn').click(function() {
                        $('.time-slot-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                        $('#appointment_time').val($(this).data('time'));
                    });
                } else {
                    $('#available-slots').html('<div class="alert alert-warning">No available slots found for the selected date. Please try another date.</div>');
                }
            },
            error: function() {
                $('#available-slots').html('<div class="alert alert-danger">Error loading available slots. Please try again.</div>');
            }
        });
    }
    
    // Form submission
    $('#referral-appointment-form').submit(function(e) {
        e.preventDefault();
        
        if (!$('#appointment_time').val()) {
            alert('Please select an available time slot.');
            return false;
        }
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.status) {
                    alert('Appointment booked successfully!');
                    window.location.href = '{{ route("backend.patientreferral.show", $referral) }}';
                } else {
                    alert('Error booking appointment: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON ? xhr.responseJSON.message : 'Error booking appointment';
                alert(message);
            }
        });
    });
});
</script>
@endsection
