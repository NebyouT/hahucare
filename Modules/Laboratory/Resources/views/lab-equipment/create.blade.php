@extends('backend.layouts.app')

@section('title') {{ __('Create Lab Equipment') }} @endsection

@section('content')
<x-backend.section-header>
    <div>
        <x-backend.breadcrumbs>
            <x-backend.breadcrumb-item route='{{ route("backend.lab-equipment.index") }}' icon='ph ph-gear'>
                {{ __('Lab Equipment') }}
            </x-backend.breadcrumb-item>
            <x-backend.breadcrumb-item type="active">{{ __('Create') }}</x-backend.breadcrumb-item>
        </x-backend.breadcrumbs>
    </div>
    <x-slot name="toolbar">
        <a href="{{ route('backend.lab-equipment.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="{{ __('Back') }}">
            <i class="ph ph-arrow-left"></i> {{ __('Back') }}
        </a>
    </x-slot>
</x-backend.section-header>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('backend.lab-equipment.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Equipment Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="equipment_name" class="form-control" value="{{ old('equipment_name') }}" required>
                                @error('equipment_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Equipment Code') }}<span class="text-danger">*</span></label>
                                <input type="text" name="equipment_code" class="form-control" value="{{ old('equipment_code') }}" required>
                                @error('equipment_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Manufacturer') }}</label>
                                <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer') }}">
                                @error('manufacturer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Model Number') }}</label>
                                <input type="text" name="model_number" class="form-control" value="{{ old('model_number') }}">
                                @error('model_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Serial Number') }}</label>
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                                @error('serial_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Location') }}</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                                @error('location')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Purchase Date') }}</label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}">
                                @error('purchase_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Warranty Expiry') }}</label>
                                <input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry') }}">
                                @error('warranty_expiry')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Last Maintenance Date') }}</label>
                                <input type="date" name="last_maintenance_date" class="form-control" value="{{ old('last_maintenance_date') }}">
                                @error('last_maintenance_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Next Maintenance Date') }}</label>
                                <input type="date" name="next_maintenance_date" class="form-control" value="{{ old('next_maintenance_date') }}">
                                @error('next_maintenance_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph ph-floppy-disk"></i> {{ __('Save') }}
                                </button>
                                <a href="{{ route('backend.lab-equipment.index') }}" class="btn btn-secondary">
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
