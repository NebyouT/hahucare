@extends('backend.layouts.app')

@section('title') {{ __('Edit Lab Category') }} @endsection

@section('content')

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Edit Lab Category') }}</h5>
            <a href="{{ route('backend.lab-categories.index') }}" class="btn btn-secondary btn-sm">
                <i class="ph ph-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>
        <div class="card-body">
                    <form method="POST" action="{{ route('backend.lab-categories.update', $category->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Slug') }}</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
                                @error('slug')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Sort Order') }}</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}">
                                @error('sort_order')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
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
                                <a href="{{ route('backend.lab-categories.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
        </div>
    </div>
</div>
@endsection
