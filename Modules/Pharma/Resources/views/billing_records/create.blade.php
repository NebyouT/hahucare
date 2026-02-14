@extends('pharma::layouts.app')

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">
            <div class="d-flex justify-content-end">
                <a href="{{ route('backend.medicine-category.index') }}" class="btn btn-primary" data-type="ajax"
                    data-bs-toggle="tooltip">
                    {{ __('messages.back') }}
                </a>
            </div>
        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="category-form" method="POST"
                            action="{{ $medicineForm->exists ? route('backend.medicine-form.update', $medicineForm->id) : route('backend.medicine-form.store') }}"
                            novalidate>
                            @csrf
                            @if ($medicineForm->exists)
                                @method('PUT')
                            @endif

                            <div class="row align-items-center">
                                <!-- Category Name -->
                                <div class="form-group col-md-6">
                                    <label for="name" class="form-label">
                                        {{ __('Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        placeholder="{{ __('Name') }}"
                                        value="{{ old('name', $medicineForm->name ?? '') }}">
                                    <div class="invalid-feedback">
                                        {{ __('pharma::messages.please_provide_valid_name') }}
                                    </div>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <label for="status" class="form-label mb-0">
                                            {{ __('pharma::messages.lbl_status') }}
                                        </label>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox" name="status" id="status" class="form-check-input"
                                                value="1"
                                                {{ old('status', $medicineForm->status ?? 1) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-md btn-primary float-end d-flex align-items-center justify-content-center gap-2"
                                id="submit-button">
                                <span id="button-loader" class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                <span id="button-text">{{ $medicineForm->exists ? __('Update') : __('Create') }}</span>
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>
        $(document).ready(function() {
            const form = $('#category-form');
            const submitButton = $('#submit-button');
            const loader = $('#button-loader');

            form.on('submit', function(event) {
                if (!form[0].checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    loader.addClass('d-none');
                    submitButton.prop('disabled', false);
                } else {

                    loader.removeClass('d-none');
                    submitButton.prop('disabled', true);
                }


            });
        });
    </script>
@endpush
