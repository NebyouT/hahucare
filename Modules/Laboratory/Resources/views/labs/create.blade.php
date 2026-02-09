@extends('backend.layouts.app')

@section('title', 'Create Lab')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Add New Lab</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.labs.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Lab Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="lab_code" class="form-label">Lab Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('lab_code') is-invalid @enderror" 
                                           id="lab_code" name="lab_code" value="{{ old('lab_code') }}" required>
                                    @error('lab_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                                    <select class="form-select @error('clinic_id') is-invalid @enderror" 
                                            id="clinic_id" name="clinic_id" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                                {{ $clinic->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('clinic_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                           id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="time_slot_duration" class="form-label">Time Slot Duration (minutes)</label>
                                    <input type="number" class="form-control @error('time_slot_duration') is-invalid @enderror" 
                                           id="time_slot_duration" name="time_slot_duration" value="{{ old('time_slot_duration', 30) }}" min="15">
                                    @error('time_slot_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" value="{{ old('country') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" 
                                           name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Featured
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('backend.labs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Lab
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
