@extends('pharma::layouts.app')
@section('title')
    {{ __($isEdit ? $edit_module_title : $module_title) }}
@endsection
@section('content')
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            <h1 class="h3 text-gray-800">
                {{ $medicineCategory->exists ? __('pharma::messages.edit_medicine_category') : __('pharma::messages.add_medicine_category') }}
            </h1>
        </div>
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
                <form id="category-form" method="POST"
                    action="{{ $medicineCategory->exists ? route('backend.medicine-category.update', $medicineCategory->id) : route('backend.medicine-category.store') }}"
                    novalidate>
                    @csrf
                    @if ($medicineCategory->exists)
                        @method('PUT')
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Category Name -->
                                <div class="form-group col-md-6">
                                    <label for="name" class="form-label">
                                        {{ __('messages.name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        placeholder="{{ __('messages.name') }}"
                                        value="{{ old('name', $medicineCategory->name ?? '') }}">
                                    <div class="text-danger d-none" id="name-error">
                                        {{ __('pharma::messages.please_provide_valid_name') }}
                                    </div>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <label for="status" class="form-label mb-0">
                                            {{ __('messages.status') }}
                                        </label>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox" name="status" id="status" class="form-check-input"
                                                value="1"
                                                {{ old('status', $medicineCategory->status ?? 1) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3" id="form-buttons">
                        <button type="button" class="btn btn btn-white me-2"
                            id="cancel-btn">{{ __('pharma::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-secondary" id="submit-button">
                            <span id="button-loader" class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            <span id="button-text">{{ __('pharma::messages.save') }}</span>
                        </button>
                    </div>
                </form>
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
                let nameInput = $('#name');
                let nameError = $('#name-error');
                const isNameEmpty = !nameInput.val().trim();
                if (!form[0].checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    // Hide loader and enable the button if validation fails
                    nameError.removeClass('d-none');
                    nameInput.addClass('is-invalid');
                    loader.addClass('d-none');
                    submitButton.prop('disabled', false);
                } else {
                    // Show loader and disable the button if validation passes
                    loader.removeClass('d-none');
                    submitButton.prop('disabled', true);
                }

                // form.addClass('was-validated');
            });
            $('#cancel-btn').on('click', function() {
                Swal.fire({
                    title: '{{ __('pharma::messages.are_you_sure') }}',
                    text: '{{ __('pharma::messages.unsaved_changes_warning') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('pharma::messages.yes_cancel') }}',
                    cancelButtonText: '{{ __('pharma::messages.no_stay') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('backend.medicine-category.index') }}";
                    }
                });
            });
        });
    </script>
@endpush
