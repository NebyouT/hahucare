@extends('backend.layouts.app')

@section('title') {{ __('Edit Lab Equipment') }} @endsection

@section('content')

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Edit Lab Equipment') }}</h5>
            <a href="{{ route('backend.lab-equipment.index') }}" class="btn btn-secondary btn-sm">
                <i class="ph ph-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>
        <div class="card-body">
                    <form method="POST" action="{{ route('backend.lab-equipment.update', $equipment->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Equipment Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="equipment_name" class="form-control" value="{{ old('equipment_name', $equipment->equipment_name) }}" required>
                                @error('equipment_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Equipment Code') }}<span class="text-danger">*</span></label>
                                <input type="text" name="equipment_code" class="form-control" value="{{ old('equipment_code', $equipment->equipment_code) }}" required>
                                @error('equipment_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Manufacturer') }}</label>
                                <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer', $equipment->manufacturer) }}">
                                @error('manufacturer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Model Number') }}</label>
                                <input type="text" name="model_number" class="form-control" value="{{ old('model_number', $equipment->model_number) }}">
                                @error('model_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Serial Number') }}</label>
                                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $equipment->serial_number) }}">
                                @error('serial_number')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Location') }}</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $equipment->location) }}">
                                @error('location')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Purchase Date') }}</label>
                                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}">
                                @error('purchase_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Warranty Expiry') }}</label>
                                <input type="date" name="warranty_expiry" class="form-control" value="{{ old('warranty_expiry', $equipment->warranty_expiry?->format('Y-m-d')) }}">
                                @error('warranty_expiry')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Last Maintenance Date') }}</label>
                                <input type="date" name="last_maintenance_date" class="form-control" value="{{ old('last_maintenance_date', $equipment->last_maintenance_date?->format('Y-m-d')) }}">
                                @error('last_maintenance_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Next Maintenance Date') }}</label>
                                <input type="date" name="next_maintenance_date" class="form-control" value="{{ old('next_maintenance_date', $equipment->next_maintenance_date?->format('Y-m-d')) }}">
                                @error('next_maintenance_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="active" {{ old('status', $equipment->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
                                    <option value="inactive" {{ old('status', $equipment->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $equipment->description) }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph ph-floppy-disk"></i> {{ __('Update') }}
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
@endsection
