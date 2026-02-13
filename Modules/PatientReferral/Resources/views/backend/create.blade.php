// Modules/PatientReferral/Resources/views/backend/create.blade.php

@extends('backend.layouts.app')
@section('title', 'Add New Patient Referral')
@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const referredBySelect = document.getElementById('referred_by');
    const referredToSelect = document.getElementById('referred_to');
    
    // Store original options
    const originalReferredToOptions = Array.from(referredToSelect.options).slice(1); // Skip first empty option
    
    function updateReferredToOptions() {
        const selectedReferredBy = referredBySelect.value;
        
        // Clear current options (keep the first empty option)
        referredToSelect.innerHTML = '<option value="">Select Doctor</option>';
        
        // Add back all options except the selected one
        originalReferredToOptions.forEach(option => {
            if (option.value !== selectedReferredBy) {
                referredToSelect.appendChild(option.cloneNode(true));
            }
        });
        
        // If current selected value is the same as referred_by, clear it
        if (referredToSelect.value === selectedReferredBy) {
            referredToSelect.value = '';
        }
    }
    
    // Update referred_to options when referred_by changes
    referredBySelect.addEventListener('change', updateReferredToOptions);
    
    // Initialize on page load
    updateReferredToOptions();
});
</script>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Referral</h3>
                    <div class="card-tools">
                        <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('backend.patientreferral.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="patient_id">Patient</label>
                            <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                <option value="">Select Patient</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name ?? $patient->first_name . ' ' . $patient->last_name }} ({{ $patient->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="referred_by">Referred By (Doctor)</label>
                                <select name="referred_by" id="referred_by" class="form-control @error('referred_by') is-invalid @enderror" required>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('referred_by') == $doctor->id ? 'selected' : '' }}>
                                            Dr. {{ $doctor->full_name ?? $doctor->first_name . ' ' . $doctor->last_name }} ({{ $doctor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('referred_by')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="referred_to">Referred To (Doctor)</label>
                                <select name="referred_to" id="referred_to" class="form-control @error('referred_to') is-invalid @enderror" required>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('referred_to') == $doctor->id ? 'selected' : '' }}>
                                            Dr. {{ $doctor->full_name ?? $doctor->first_name . ' ' . $doctor->last_name }} ({{ $doctor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('referred_to')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="accepted" {{ old('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="referral_date">Referral Date</label>
                                <input type="date" name="referral_date" id="referral_date" 
                                       class="form-control @error('referral_date') is-invalid @enderror" 
                                       value="{{ old('referral_date', now()->format('Y-m-d')) }}" required>
                                @error('referral_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="reason">Reason for Referral</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="3" placeholder="Enter reason for referral..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes">Additional Notes (Optional)</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="card-footer bg-transparent p-0 pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Referral
                            </button>
                            <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection