<!-- Modules/PatientReferral/Resources/views/backend/edit.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Edit Referral')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Referral</h3>
                    <div class="card-tools">
                        <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Referrals
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.patientreferral.update', $patientReferral) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id">Patient <span class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-control" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ $patientReferral->patient_id == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            @if(auth()->user()->user_type !== 'doctor')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="referred_by">Referred By <span class="text-danger">*</span></label>
                                        <select name="referred_by" id="referred_by" class="form-control" required>
                                            <option value="">Select Doctor</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ $patientReferral->referred_by == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->first_name }} {{ $doctor->last_name }} ({{ $doctor->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('referred_by')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referred_to">Referred To <span class="text-danger">*</span></label>
                                    <select name="referred_to" id="referred_to" class="form-control" required>
                                        <option value="">Select Doctor</option>
                                        @if(isset($referredToDoctors))
                                            @foreach($referredToDoctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ $patientReferral->referred_to == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->first_name }} {{ $doctor->last_name }} ({{ $doctor->email }})
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ $patientReferral->referred_to == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->first_name }} {{ $doctor->last_name }} ({{ $doctor->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('referred_to')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="referral_date">Referral Date <span class="text-danger">*</span></label>
                                    <input type="date" name="referral_date" id="referral_date" class="form-control" 
                                           value="{{ $patientReferral->referral_date->format('Y-m-d') }}" required>
                                    @error('referral_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="pending" {{ $patientReferral->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="accepted" {{ $patientReferral->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                        <option value="rejected" {{ $patientReferral->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="reason">Reason for Referral <span class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" class="form-control" rows="3" required>{{ $patientReferral->reason }}</textarea>
                                    @error('reason')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Additional Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ $patientReferral->notes }}</textarea>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Referral
                                    </button>
                                    <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary">
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
@endsection
