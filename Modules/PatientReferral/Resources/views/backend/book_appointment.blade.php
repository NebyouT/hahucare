@php
    $patientsData = [];
    // Support both $customer, $customers and $patients variable names
    $customerList = $customer ?? ($customers ?? ($patients ?? []));
    foreach ($customerList as $patient) {
        $patientsData[$patient->id] = [
            'name' => trim($patient->first_name . ' ' . $patient->last_name),
            'email' => $patient->email ?? '',
            'mobile' => $patient->mobile ?? '',
            'created_at' => $patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('F Y') : '',
            'avatar' => $patient->profile_image ?? default_user_avatar(),
        ];
    }
    $authUserId = auth()->id();
    $currencySymbol = \App\Models\Currency::defaultSymbol();
    
    // Pre-filled referral data
    $referralPatient = $referral->patient;
    $referralDoctor = $referral->referredToDoctor;
    $referralClinic = $doctorClinic;
@endphp

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

            <!-- Appointment Booking Form - Based on new_appointment.blade.php -->
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
                    <form id="clinic-appointment-form" enctype="multipart/form-data" class="d-flex flex-column h-100">
                        @csrf
                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="appointment_id" id="appointment_id" value="">
                        <input type="hidden" name="referral_id" value="{{ $referral->id }}">
                        <input type="hidden" name="from_referral" value="true">

                        {{-- Main content without extra scroll --}}
                        <div class="flex-grow-1">
                            {{-- Patient Selection (Pre-filled and non-editable) --}}
                            <div class="mb-3">
                                <label class="form-label">{{ __('appointment.lbl_select_patient') }} <span
                                        class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="patient-select" name="patient_id" class="form-select select2" disabled>
                                        <option value="{{ $referralPatient->id }}" selected>
                                            {{ $referralPatient->first_name . ' ' . $referralPatient->last_name }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="user_id" value="{{ $referralPatient->id }}">
                                    <small class="text-muted">Pre-filled from referral (non-editable)</small>
                                </div>
                            </div>

                            {{-- Patient Details Section (Always visible for referral) --}}
                            <div id="appointment-details" class="">
                                <div class="d-flex m-0 mb-3 p-3 align-items-center gap-lg-3 gap-2 flex-wrap border bg-gray-900 rounded">
                                    <!-- Avatar -->
                                    <img id="patient-avatar" src="{{ $referralPatient->profile_image ?? default_user_avatar() }}"
                                        class="rounded-circle border object-fit-cover" width="64" height="64"
                                        alt="{{ __('appointment.lbl_avatar') }}">

                                    <!-- Patient details -->
                                    <div class="flex-grow-1">
                                        <h6 id="patient-name" class="mb-1 fw-semibold heading-color">{{ $referralPatient->first_name . ' ' . $referralPatient->last_name }}</h6>
                                        <small id="patient-since" class="d-block mb-2 text-muted">{{ $referralPatient->created_at ? \Carbon\Carbon::parse($referralPatient->created_at)->format('F Y') : '' }}</small>
                                        <div class="d-flex flex-column gap-2">
                                            <small class="text-muted">
                                                <b class="heading-color">{{ __('appointment.lbl_phone') }}:</b>
                                                <span id="patient-phone" class="text-dark">{{ $referralPatient->mobile ?? '' }}</span>
                                            </small>
                                            <small class="text-muted">
                                                <b class="heading-color">{{ __('appointment.lbl_email') }}:</b>
                                                <span id="patient-email" class="text-dark">{{ $referralPatient->email ?? '' }}</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    {{-- Clinic Selection (Pre-filled and non-editable) --}}
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('appointment.lbl_select_clinic') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <select id="clinic-select" class="form-select select2" name="clinic_id" disabled>
                                                <option value="{{ $referralClinic->id }}" selected>
                                                    {{ $referralClinic->name }}
                                                    @if($referralClinic->address)
                                                        - {{ $referralClinic->address }}
                                                    @endif
                                                </option>
                                            </select>
                                            <small class="text-muted">Pre-filled from referral (non-editable)</small>
                                        </div>
                                    </div>

                                    {{-- Doctor Selection (Pre-filled and non-editable) --}}
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('appointment.lbl_select_doctor') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <select id="doctor-select" class="form-select select2" name="doctor_id" disabled>
                                                <option value="{{ $referralDoctor->id }}" selected>
                                                    {{ $referralDoctor->first_name . ' ' . $referralDoctor->last_name }}
                                                </option>
                                            </select>
                                            <small class="text-muted">Pre-filled from referral (non-editable)</small>
                                        </div>
                                    </div>

                                    {{-- Service Selection --}}
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('appointment.lbl_select_service') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="position-relative">
                                            <select id="service-select" class="form-select select2" name="service_id"
                                                data-placeholder="{{ __('appointment.lbl_select_service') }}">
                                                <option value=""></option>
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                @endforeach
                                            </select>
                                            <div id="service-loader" class="position-absolute top-50 start-50 translate-middle d-none"
                                                role="status">
                                                <i class="fas fa-spinner fa-spin text-primary"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Appointment Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('appointment.lbl_appointment_date') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="appointment-date" name="appointment_date" class="form-control"
                                            placeholder="{{ __('appointment.lbl_appointment_date') }}"
                                            value="{{ $referral->referral_date->format('Y-m-d') }}">
                                    </div>

                                    {{-- Available Slots --}}
                                    <div class="col-12">
                                        <label class="form-label">{{ __('appointment.lbl_availble_slots') }} <span
                                                class="text-danger">*</span></label>
                                        <div id="available-slots" class="slots-container">
                                            <p class="text-muted text-center bg-gray-900 p-3 rounded">
                                                {{ __('appointment.lbl_slot_not_found') }}</p>
                                        </div>
                                    </div>

                                    {{-- Medical Report Upload --}}
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ __('appointment.lbl_medical_report') }}</label>
                                        <input type="file" id="medical-report" class="form-control" name="file_url[]" multiple
                                            accept=".jpeg, .jpg, .png, .gif, .pdf">
                                    </div>

                                    {{-- Medical History --}}
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ __('appointment.lbl_medical_history') }}</label>
                                        <textarea id="medical-history" class="form-control" name="appointment_extra_info" rows="4"
                                            placeholder="{{ __('appointment.lbl_medical_history_placeholder') }}">{{ $referral->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fixed bottom section - Pricing --}}
                        <div class="mt-auto">
                            <div class="custom-pricing-box mt-4 bg-gray-900 p-3 rounded border">
                                <div class="custom-pricing-row d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                                    <span class="custom-label" id="service-price-label">{{ __('appointment.lbl_service_price') }}:</span>
                                    <span class="custom-value text-end text-primary fw-bold"
                                        id="service-price">{{ $currencySymbol }}0.00</span>
                                </div>

                                <div class="custom-pricing-row d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2 d-none"
                                    id="discount-row">
                                    <span class="custom-label" id="discount-label">{{ __('appointment.lbl_discount') }}:</span>
                                    <span class="custom-value text-success" id="discount-amount">-{{ $currencySymbol }}0.00</span>
                                </div>

                                <div class="custom-pricing-row d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2 d-none"
                                    id="subtotal-row">
                                    <span class="custom-label" id="subtotal-label">{{ __('appointment.lbl_subtotal') }}:</span>
                                    <span class="custom-value" id="subtotal-amount">{{ $currencySymbol }}0.00</span>
                                </div>

                                <!-- Inline tax (dynamic) -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <span class="font-size-14">{{ __('appointment.lbl_tax') }}</span>
                                    <div class="cursor-pointer applied-tax" data-bs-toggle="collapse" data-bs-target="#applied-tax"
                                        aria-expanded="false">
                                        <i class="ph ph-caret-down fw-semibold" id="tax-caret-icon"></i>
                                        <span class="text-danger h6 m-0" id="tax-inline-amount">{{ $currencySymbol }}0.00</span>
                                    </div>
                                </div>
                                <div id="applied-tax" class="mt-2 p-3 card m-0 rounded collapse">
                                    <h6 class="font-size-14">{{ __('appointment.lbl_applied_tax') }}</h6>
                                    <div id="applied-tax-inline">
                                        <div class="text-center bg-body py-3 rounded">
                                            <i class="ph ph-receipt mb-2 fs-2"></i>
                                            <p class="mb-0">{{ __('appointment.lbl_no_taxes_applied') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="custom-pricing-divider"></div>
                                <div class="custom-pricing-row d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                                    <span class="custom-label fw-bold">{{ __('appointment.lbl_total_amount') }}:</span>
                                    <span class="custom-value text-success fw-bold" id="total-amount">{{ $currencySymbol }}0.00</span>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="d-grid d-sm-flex justify-content-sm-end gap-3 p-3">
                                <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-white d-block">
                                    <i class="fas fa-times"></i> {{ __('messages.close') }}
                                </a>
                                <button class="btn btn-secondary" name="submit" id="appointment-submit">
                                    <i class="fas fa-calendar-plus"></i> {{ __('messages.save') }}
                                </button>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Global variables
    const baseMeta = document.querySelector('meta[name="baseUrl"]');
    const baseUrl = baseMeta?.getAttribute('content') || window.location.origin;
    
    // Form elements
    const form = document.getElementById('clinic-appointment-form');
    const patientSelect = document.getElementById('patient-select');
    const clinicSelect = document.getElementById('clinic-select');
    const doctorSelect = document.getElementById('doctor-select');
    const serviceSelect = document.getElementById('service-select');
    const appointmentDate = document.getElementById('appointment-date');
    const availableSlots = document.getElementById('available-slots');
    const submitBtn = document.getElementById('appointment-submit');
    
    // Pricing elements
    const servicePriceEl = document.getElementById('service-price');
    const discountAmountEl = document.getElementById('discount-amount');
    const subtotalAmountEl = document.getElementById('subtotal-amount');
    const taxInlineAmountEl = document.getElementById('tax-inline-amount');
    const totalAmountEl = document.getElementById('total-amount');
    
    // Pre-filled values from referral
    const selectedPatientId = parseInt("{{ $referralPatient->id }}");
    const selectedClinicId = parseInt("{{ $referralClinic->id }}");
    const selectedDoctorId = parseInt("{{ $referralDoctor->id }}");
    
    let selectedSlot = '';
    let currentServiceData = null;
    let taxData = [];

    // Initialize Select2 for enabled selects
    function initializeFormSelect2() {
        if (serviceSelect && !serviceSelect.disabled) {
            $(serviceSelect).select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
    }

    // Load services for the specific doctor and clinic
    function loadServices() {
        if (!selectedDoctorId || !selectedClinicId) return;
        
        $('#service-loader').removeClass('d-none');
        serviceSelect.innerHTML = '<option value=""></option>';
        
        const url = baseUrl + '/app/services/index_list?doctorId=' + encodeURIComponent(selectedDoctorId) + '&clinicId=' + encodeURIComponent(selectedClinicId);
        
        fetch(url)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                const items = Array.isArray(data) ? data : (data?.results || data?.data || data?.list || []);
                items.forEach(function(service) {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    serviceSelect.appendChild(option);
                });
                
                // Reinitialize select2
                if ($(serviceSelect).data('select2')) {
                    $(serviceSelect).select2('destroy');
                }
                initializeFormSelect2();
            })
            .catch(function(error) {
                console.error('Error loading services:', error);
            })
            .finally(function() {
                $('#service-loader').addClass('d-none');
            });
    }

    // Load available time slots
    function loadAvailableSlots() {
        selectedSlot = '';
        availableSlots.innerHTML = '<p class="text-muted text-center bg-gray-900 p-3 rounded">{{ __("appointment.lbl_slot_not_found") }}</p>';
        
        const date = appointmentDate.value;
        if (!date || !selectedDoctorId || !selectedClinicId || !serviceSelect.value) return;
        
        const url = baseUrl + '/app/doctor/get-available-slot?appointment_date=' + encodeURIComponent(date) + '&doctor_id=' + encodeURIComponent(selectedDoctorId) + '&clinic_id=' + encodeURIComponent(selectedClinicId) + '&service_id=' + encodeURIComponent(serviceSelect.value);
        
        fetch(url)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                const slots = data?.availableSlot || [];
                if (slots.length === 0) {
                    availableSlots.innerHTML = '<p class="text-muted text-center bg-gray-900 p-3 rounded">{{ __("appointment.lbl_slot_not_found") }}</p>';
                    return;
                }
                
                availableSlots.innerHTML = '';
                slots.forEach(function(slot) {
                    const slotElement = document.createElement('div');
                    slotElement.className = 'slot-item clickable-text';
                    slotElement.innerHTML = '<label class="form-check-label m-0 p-2 d-block text-center border rounded cursor-pointer"><input type="radio" name="appointment_time" value="' + slot + '" class="d-none">' + slot + '</label>';
                    
                    slotElement.addEventListener('click', function() {
                        document.querySelectorAll('.slot-item').forEach(function(item) {
                            item.classList.remove('selected');
                        });
                        slotElement.classList.add('selected');
                        selectedSlot = slot;
                        document.querySelector('input[name="appointment_time"]').value = slot;
                    });
                    
                    availableSlots.appendChild(slotElement);
                });
            })
            .catch(function(error) {
                console.error('Error loading slots:', error);
            });
    }

    // Calculate service pricing
    function calculateServicePrice() {
        if (!serviceSelect.value || !selectedDoctorId || !selectedClinicId) {
            resetPricing();
            return;
        }
        
        const url = baseUrl + '/app/services/service-price?service_id=' + encodeURIComponent(serviceSelect.value) + '&doctor_id=' + encodeURIComponent(selectedDoctorId);
        
        fetch(url)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                currentServiceData = data;
                updatePricingDisplay(data);
            })
            .catch(function(error) {
                console.error('Error calculating price:', error);
            });
    }

    // Update pricing display
    function updatePricingDisplay(data) {
        const basePrice = data?.base_price || 0;
        const serviceCharge = data?.service_charge || 0;
        const discountAmount = data?.discount_amount || 0;
        const inclusiveTax = data?.inclusive_tax_data_total || 0;
        const totalAmount = data?.total_amount || 0;
        
        // Update service price
        servicePriceEl.textContent = '{{ $currencySymbol }}' + parseFloat(basePrice).toFixed(2);
        
        // Update discount if applicable
        if (discountAmount > 0) {
            document.getElementById('discount-row').classList.remove('d-none');
            discountAmountEl.textContent = '-{{ $currencySymbol }}' + parseFloat(discountAmount).toFixed(2);
            document.getElementById('subtotal-row').classList.remove('d-none');
            subtotalAmountEl.textContent = '{{ $currencySymbol }}' + parseFloat(serviceCharge - discountAmount).toFixed(2);
        } else {
            document.getElementById('discount-row').classList.add('d-none');
            document.getElementById('subtotal-row').classList.add('d-none');
        }
        
        // Calculate and display tax
        loadAndDisplayTax(totalAmount);
        
        // Update total
        totalAmountEl.textContent = '{{ $currencySymbol }}' + parseFloat(totalAmount).toFixed(2);
    }

    // Load and display tax information
    function loadAndDisplayTax(amount) {
        const url = baseUrl + '/app/tax/index_list?module_type=services&tax_type=exclusive';
        
        fetch(url)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                taxData = Array.isArray(data) ? data : (data?.results || data?.data || data?.list || []);
                const totalTax = calculateExclusiveTax(amount);
                taxInlineAmountEl.textContent = '{{ $currencySymbol }}' + parseFloat(totalTax).toFixed(2);
                
                // Update tax breakdown
                updateTaxBreakdown();
            })
            .catch(function(error) {
                console.error('Error loading tax data:', error);
            });
    }

    // Calculate exclusive tax
    function calculateExclusiveTax(amount) {
        let total = 0;
        (taxData || []).forEach(function(item) {
            const type = item.type;
            const value = parseFloat(item.value ?? 0);
            let add = 0;
            if (type === 'fixed') {
                add = value;
            } else if (type === 'percent') {
                add = amount * (value / 100);
            }
            if (add > 0) {
                total += add;
            }
        });
        return total;
    }

    // Update tax breakdown display
    function updateTaxBreakdown() {
        const appliedTaxInline = document.getElementById('applied-tax-inline');
        
        if (taxData.length === 0) {
            appliedTaxInline.innerHTML = '<div class="text-center bg-body py-3 rounded"><i class="ph ph-receipt mb-2 fs-2"></i><p class="mb-0">{{ __("appointment.lbl_no_taxes_applied") }}</p></div>';
            return;
        }
        
        let html = '';
        taxData.forEach(function(item) {
            const title = item.title || item.name || 'Tax';
            const type = item.type;
            const value = parseFloat(item.value ?? 0);
            const amount = type === 'percent' 
                ? (parseFloat(totalAmountEl.textContent.replace(/[^0-9.-]/g, '')) * value / 100)
                : value;
            
            html += '<div class="d-flex justify-content-between align-items-center mb-2"><span>' + title + ' (' + (type === 'percent' ? value + '%' : '$' + value) + ')</span><span class="fw-bold">{{ $currencySymbol }}' + amount.toFixed(2) + '</span></div>';
        });
        
        appliedTaxInline.innerHTML = html;
    }

    // Reset pricing display
    function resetPricing() {
        servicePriceEl.textContent = '{{ $currencySymbol }}0.00';
        discountAmountEl.textContent = '-{{ $currencySymbol }}0.00';
        subtotalAmountEl.textContent = '{{ $currencySymbol }}0.00';
        taxInlineAmountEl.textContent = '{{ $currencySymbol }}0.00';
        totalAmountEl.textContent = '{{ $currencySymbol }}0.00';
        
        document.getElementById('discount-row').classList.add('d-none');
        document.getElementById('subtotal-row').classList.add('d-none');
        
        document.getElementById('applied-tax-inline').innerHTML = '<div class="text-center bg-body py-3 rounded"><i class="ph ph-receipt mb-2 fs-2"></i><p class="mb-0">{{ __("appointment.lbl_no_taxes_applied") }}</p></div>';
    }

    // Form validation
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(function(el) {
            el.remove();
        });
        
        // Check service selection
        if (!serviceSelect.value) {
            serviceSelect.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'Please select a service';
            serviceSelect.parentNode.appendChild(errorDiv);
            isValid = false;
        }
        
        // Check appointment date
        if (!appointmentDate.value) {
            appointmentDate.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'Please select appointment date';
            appointmentDate.parentNode.appendChild(errorDiv);
            isValid = false;
        }
        
        // Check time slot selection
        if (!selectedSlot) {
            availableSlots.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'Please select an appointment time';
            availableSlots.parentNode.appendChild(errorDiv);
            isValid = false;
        }
        
        return isValid;
    }

    // Form submission
    function submitForm(e) {
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
        
        // Add additional required fields
        payload.status = 'confirmed';
        payload.appointment_time = selectedSlot;
        
        // AJAX request
        fetch(baseUrl + '/app/appointment', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            body: JSON.stringify(payload)
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP error! Status: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            loadingModal.hide();
            
            if (data.status || data.success) {
                // Show success message
                const successToast = new bootstrap.Toast(document.getElementById('successToast'));
                successToast.show();
                
                // Redirect after delay
                setTimeout(function() {
                    window.location.href = '{{ route("backend.patientreferral.show", $referral) }}';
                }, 2000);
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(function(error) {
            loadingModal.hide();
            console.error('Error:', error);
            
            // Show error message
            document.getElementById('errorToastBody').textContent = 'Error booking appointment: ' + error.message;
            const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
            errorToast.show();
        });
    }

    // Event listeners
    serviceSelect.addEventListener('change', function() {
        loadAvailableSlots();
        calculateServicePrice();
    });

    appointmentDate.addEventListener('change', loadAvailableSlots);
    submitBtn.addEventListener('click', submitForm);

    // Tax section toggle
    document.getElementById('applied-tax').addEventListener('show.bs.collapse', function() {
        document.getElementById('tax-caret-icon').classList.remove('ph-caret-down');
        document.getElementById('tax-caret-icon').classList.add('ph-caret-up');
    });

    document.getElementById('applied-tax').addEventListener('hide.bs.collapse', function() {
        document.getElementById('tax-caret-icon').classList.remove('ph-caret-up');
        document.getElementById('tax-caret-icon').classList.add('ph-caret-down');
    });

    // Initialize date picker
    flatpickr(appointmentDate, {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        static: true
    });

    // Initialize the form
    initializeFormSelect2();
    loadServices();
});
</script>
@endpush

@push('styles')
<style>
.slots-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    max-height: 200px;
    overflow-y: auto;
}

.slot-item {
    flex: 0 0 auto;
    min-width: 80px;
}

.slot-item.selected label {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.slot-item:hover label {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
}

.custom-pricing-box {
    border: 1px solid #dee2e6;
}

.custom-pricing-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 0.5rem 0;
}

.clickable-text {
    cursor: pointer;
    user-select: none;
}

.bg-gray-900 {
    background-color: #f8f9fa !important;
}

.toast {
    min-width: 300px;
}
</style>
@endpush