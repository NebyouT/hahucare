@extends('backend.layouts.app')

@section('title') {{ __('Create Lab Test') }} @endsection

@section('content')

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Create Lab Test') }}</h5>
            <a href="{{ route('backend.lab-tests.index') }}" class="btn btn-secondary btn-sm">
                <i class="ph ph-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>
        <div class="card-body">
                    <form method="POST" action="{{ route('backend.lab-tests.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Test Code') }}<span class="text-danger">*</span></label>
                                <input type="text" name="test_code" class="form-control" value="{{ old('test_code') }}" required>
                                @error('test_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Test Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="test_name" class="form-control" value="{{ old('test_name') }}" required>
                                @error('test_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control select2">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Price') }}<span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                                @error('price')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Discount Price') }}</label>
                                <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price') }}">
                                @error('discount_price')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Discount Type') }}</label>
                                <select name="discount_type" class="form-control">
                                    <option value="">{{ __('Select Type') }}</option>
                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>{{ __('Percentage') }}</option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>{{ __('Fixed') }}</option>
                                </select>
                                @error('discount_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Duration (Minutes)') }}</label>
                                <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes') }}">
                                @error('duration_minutes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Sample Type') }}</label>
                                <input type="text" name="sample_type" class="form-control" value="{{ old('sample_type') }}" placeholder="e.g., Blood, Urine">
                                @error('sample_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Normal Range') }}</label>
                                <input type="text" name="normal_range" class="form-control" value="{{ old('normal_range') }}" placeholder="e.g., 70-100 mg/dL">
                                @error('normal_range')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Unit of Measurement') }}</label>
                                <input type="text" name="unit_of_measurement" class="form-control" value="{{ old('unit_of_measurement') }}" placeholder="e.g., mg/dL">
                                @error('unit_of_measurement')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Reporting Time') }}</label>
                                <input type="text" name="reporting_time" class="form-control" value="{{ old('reporting_time') }}" placeholder="e.g., 24 hours">
                                @error('reporting_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Preparation Instructions') }}</label>
                                <textarea name="preparation_instructions" class="form-control" rows="3">{{ old('preparation_instructions') }}</textarea>
                                @error('preparation_instructions')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph ph-floppy-disk"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('backend.lab-tests.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
        </div>
    </div>
</div>
@endsection
