@extends('backend.layouts.app')

@section('title') {{ __('Edit Lab Result') }} @endsection

@section('content')
<x-backend.section-header>
    <div>
        <x-backend.breadcrumbs>
            <x-backend.breadcrumb-item route='{{ route("backend.lab-results.index") }}' icon='ph ph-file-text'>
                {{ __('Lab Results') }}
            </x-backend.breadcrumb-item>
            <x-backend.breadcrumb-item type="active">{{ __('Edit') }}</x-backend.breadcrumb-item>
        </x-backend.breadcrumbs>
    </div>
    <x-slot name="toolbar">
        <a href="{{ route('backend.lab-results.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="{{ __('Back') }}">
            <i class="ph ph-arrow-left"></i> {{ __('Back') }}
        </a>
    </x-slot>
</x-backend.section-header>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('backend.lab-results.update', $labResult->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Result Code') }}<span class="text-danger">*</span></label>
                                <input type="text" name="result_code" class="form-control" value="{{ old('result_code', $labResult->result_code) }}" required>
                                @error('result_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Lab Test') }}<span class="text-danger">*</span></label>
                                <select name="lab_test_id" class="form-control select2" required>
                                    <option value="">{{ __('Select Lab Test') }}</option>
                                    @foreach($labTests as $test)
                                        <option value="{{ $test->id }}" {{ old('lab_test_id', $labResult->lab_test_id) == $test->id ? 'selected' : '' }}>
                                            {{ $test->test_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lab_test_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Patient ID') }}<span class="text-danger">*</span></label>
                                <input type="number" name="patient_id" class="form-control" value="{{ old('patient_id', $labResult->patient_id) }}" required>
                                @error('patient_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Doctor ID') }}</label>
                                <input type="number" name="doctor_id" class="form-control" value="{{ old('doctor_id', $labResult->doctor_id) }}">
                                @error('doctor_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Test Date') }}<span class="text-danger">*</span></label>
                                <input type="date" name="test_date" class="form-control" value="{{ old('test_date', $labResult->test_date?->format('Y-m-d')) }}" required>
                                @error('test_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Result Date') }}</label>
                                <input type="date" name="result_date" class="form-control" value="{{ old('result_date', $labResult->result_date?->format('Y-m-d')) }}">
                                @error('result_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Sample Type') }}</label>
                                <input type="text" name="sample_type" class="form-control" value="{{ old('sample_type', $labResult->sample_type) }}" placeholder="e.g., Blood, Urine">
                                @error('sample_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Sample ID') }}</label>
                                <input type="text" name="sample_id" class="form-control" value="{{ old('sample_id', $labResult->sample_id) }}">
                                @error('sample_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Result Value') }}</label>
                                <input type="text" name="result_value" class="form-control" value="{{ old('result_value', $labResult->result_value) }}">
                                @error('result_value')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }}<span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="pending" {{ old('status', $labResult->status) == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    <option value="in_progress" {{ old('status', $labResult->status) == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                    <option value="completed" {{ old('status', $labResult->status) == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                    <option value="approved" {{ old('status', $labResult->status) == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <textarea name="remarks" class="form-control" rows="4">{{ old('remarks', $labResult->remarks) }}</textarea>
                                @error('remarks')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph ph-floppy-disk"></i> {{ __('Update') }}
                                </button>
                                <a href="{{ route('backend.lab-results.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
