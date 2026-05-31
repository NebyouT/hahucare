

@extends('backend.layouts.app')
@section('title', 'Add New Patient Referral')
@push('after-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for searchable dropdowns
    $('#patient_id').select2({
        placeholder: 'Search and Select Patient',
        allowClear: true,
        width: '100%'
    });
    $('#referred_by').select2({
        placeholder: 'Search and Select Doctor',
        allowClear: true,
        width: '100%'
    });
    $('#referred_to').select2({
        placeholder: 'Search and Select Doctor',
        allowClear: true,
        width: '100%'
    });

    const referredBySelect = document.getElementById('referred_by');
    const referredToSelect = document.getElementById('referred_to');
    
    function updateReferredToOptions() {
        const selectedReferredBy = referredBySelect.value;
        const currentReferredTo = referredToSelect.value;
        
        // Clear current options (keep the first empty option)
        referredToSelect.innerHTML = '<option value="">Select Doctor</option>';
        
        // Get original options from jQuery data
        const allOptions = @json($doctors->map(function($d) { return ['id' => $d->id, 'name' => 'Dr. ' . ($d->full_name ?? $d->first_name . ' ' . $d->last_name)]; }));
        const referredToOptions = @json(isset($referredToDoctors) ? $referredToDoctors->map(function($d) { return ['id' => $d->id, 'name' => 'Dr. ' . ($d->full_name ?? $d->first_name . ' ' . $d->last_name)]; }) : $doctors->map(function($d) { return ['id' => $d->id, 'name' => 'Dr. ' . ($d->full_name ?? $d->first_name . ' ' . $d->last_name)]; }));
        
        const options = referredToOptions.length ? referredToOptions : allOptions;
        
        options.forEach(function(opt) {
            if (opt.id != selectedReferredBy) {
                const option = document.createElement('option');
                option.value = opt.id;
                option.text = opt.name;
                if (opt.id == currentReferredTo) {
                    option.selected = true;
                }
                referredToSelect.appendChild(option);
            }
        });
        
        // Reinitialize Select2
        $('#referred_to').select2({
            placeholder: 'Search and Select Doctor',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Update referred_to options when referred_by changes
    $('#referred_by').on('change', updateReferredToOptions);
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
                                            Dr. {{ $doctor->full_name ?? $doctor->first_name . ' ' . $doctor->last_name }}
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
                                            Dr. {{ $doctor->full_name ?? $doctor->first_name . ' ' . $doctor->last_name }}
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