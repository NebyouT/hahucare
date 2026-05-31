<!-- Modules/PatientReferral/Resources/views/backend/create_advanced.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Create Advanced Referral')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('backend.patientreferral.store-advanced') }}">
                        @csrf
                        <input type="hidden" name="referral_type" value="advanced">
                        
                        <!-- Patient Selection -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="patient_id" id="patient_id" required onchange="loadPatientData(this.value)">
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->id }})</option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referral_date">Referral Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="referral_date" id="referral_date" value="{{ now()->format('Y-m-d') }}" required>
                                    @error('referral_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Patient Demographics (Auto-filled) -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Patient Information</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="text" class="form-control" name="patient_age" id="patient_age" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Sex</label>
                                    <input type="text" class="form-control" name="patient_sex" id="patient_sex" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" name="patient_address" id="patient_address" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Referral Details -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Referral Details</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referred_by">Referred By (Doctor) <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="referred_by" id="referred_by" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->first_name }} {{ $doctor->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('referred_by')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referred_to">Referred To (Doctor) <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="referred_to" id="referred_to" required>
                                        <option value="">Select Doctor</option>
                                        @foreach(($allDoctors ?? $doctors) as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->first_name }} {{ $doctor->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('referred_to')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referring_faculty">Referring Faculty</label>
                                    <input type="text" class="form-control" name="referring_faculty" id="referring_faculty" placeholder="e.g., Cardiology">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receiving_faculty">Receiving Faculty</label>
                                    <input type="text" class="form-control" name="receiving_faculty" id="receiving_faculty" placeholder="e.g., Neurology">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="referring_clinic_name">Referring Clinic Name</label>
                                    <input type="text" class="form-control" name="referring_clinic_name" id="referring_clinic_name" placeholder="Enter clinic name">
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Medical Information</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="chief_complaint">Chief Complaint <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="chief_complaint" id="chief_complaint" rows="2" required placeholder="Enter chief complaint"></textarea>
                                    @error('chief_complaint')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="history_findings">History and Findings</label>
                                    <textarea class="form-control" name="history_findings" id="history_findings" rows="3" placeholder="Enter history and clinical findings"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="diagnosis">Diagnosis</label>
                                    <textarea class="form-control" name="diagnosis" id="diagnosis" rows="2" placeholder="Enter diagnosis"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="treatment_given">Treatment Given</label>
                                    <textarea class="form-control" name="treatment_given" id="treatment_given" rows="2" placeholder="Enter treatments administered"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="investigation_done">Investigation Done</label>
                                    <textarea class="form-control" name="investigation_done" id="investigation_done" rows="2" placeholder="Enter investigations performed"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Past Encounters Selection -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Past Encounters</h5>
                                <p class="text-muted">Select which past encounters to include in this referral</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div id="encounters-container" class="p-3 bg-light rounded" style="min-height: 100px;">
                                        <p class="text-muted text-center">Select a patient to load their past encounters</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason for Referral -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="reason">Reason for Referral <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="reason" id="reason" rows="2" required placeholder="Enter reason for referral"></textarea>
                                    @error('reason')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Contact Information</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contact_information">Contact Information</label>
                                    <textarea class="form-control" name="contact_information" id="contact_information" rows="2" placeholder="Enter contact information"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Additional Notes</label>
                                    <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Enter any additional notes"></textarea>
                                    @error('notes')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Referral
                                </button>
                                <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Header placed below form -->
                <div class="card-footer">
                    <h3 class="card-title">Create Advanced Referral</h3>
                    <div class="card-tools float-end">
                        <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .select2 {
        width: 100%;
    }
</style>
@endpush

@push('scripts')
<script>
    function loadPatientData(patientId) {
        if (!patientId) {
            $('#patient_age').val('');
            $('#patient_sex').val('');
            $('#patient_address').val('');
            $('#encounters-container').html('<p class="text-muted text-center">Select a patient to load their past encounters</p>');
            return;
        }

        $.ajax({
            url: '/api/patient-referral/patient-data/' + patientId,
            method: 'GET',
            success: function(response) {
                $('#patient_age').val(response.age);
                $('#patient_sex').val(response.sex);
                $('#patient_address').val(response.address);
                
                // Load encounters
                if (response.encounters && response.encounters.length > 0) {
                    let html = '<div class="row">';
                    response.encounters.forEach(function(encounter) {
                        html += `
                            <div class="col-md-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="encounter_ids[]" 
                                           id="encounter_${encounter.id}" value="${encounter.id}">
                                    <label class="custom-control-label" for="encounter_${encounter.id}">
                                        ${encounter.date} - ${encounter.type}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#encounters-container').html(html);
                } else {
                    $('#encounters-container').html('<p class="text-muted text-center">No past encounters found</p>');
                }
            },
            error: function() {
                $('#encounters-container').html('<p class="text-danger text-center">Error loading patient data</p>');
            }
        });
    }

    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush
