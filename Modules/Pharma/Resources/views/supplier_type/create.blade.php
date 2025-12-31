@extends('pharma::layouts.app')

@section('title')
    {{ __($isEdit ? $edit_module_title : $add_module_title) }}
@endsection

@section('content')


    <div class="container-fluid">
        <h4 class="mb-4">{{ $supplierType->exists ? __('pharma::messages.edit_supplier_type') : __('pharma::messages.add_supplier_type') }}</h4>
        <div class="row">
            <form id="category-form" method="POST"
                action="{{ $supplierType->exists ? route('backend.supplier-type.update', $supplierType->id) : route('backend.supplier-type.store') }}"
                novalidate>
                @csrf
                @if($supplierType->exists)
                    @method('PUT')
                @endif
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Category Name -->
                                <input type="hidden" name="id" value="{{ $supplierType->id }}">
                                <div class="form-group col-md-6">
                                    <label for="name" class="form-label">
                                        {{ __('Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control" required
                                        placeholder="{{ __('Name') }}"
                                        value="{{ old('name', $supplierType->name ?? '') }}">
                                    <div class="invalid-feedback">
                                        {{ __('pharma::validation.The_name_field_is_required') }}
                                    </div>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="form-group col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <label for="status" class="form-label mb-0">
                                            {{ __('Status') }}
                                        </label>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox" name="status" id="status" class="form-check-input"
                                                value="1" {{ old('status', $supplierType->status ?? 1) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-3 mt-5" id="form-buttons">
                    <button type="button" class="btn btn-white" id="cancel-btn">
                        {{ __('pharma::messages.cancel') }}
                    </button>
                
                    <button type="submit" class="btn btn-secondary" id="submit-button">
                        <span id="button-loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span id="button-text">
                            {{ __('pharma::messages.save') }}
                        </span>
                    </button>
                </div>                          
                </form>
          </div>
    </div>
@endsection
@push('after-scripts')
<script>
    $(document).ready(function () {
        const form = $('#category-form');
        const submitButton = $('#submit-button');
        const loader = $('#button-loader');

        // Submit Button Logic
        form.on('submit', function (event) {
            if (!form[0].checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                loader.addClass('d-none');
                submitButton.prop('disabled', false);
            } else {
                loader.removeClass('d-none');
                submitButton.prop('disabled', true);
            }

            // form.addClass('was-validated');
        });

        // Cancel Button with SweetAlert
        $('#cancel-btn').on('click', function() {
            Swal.fire({
                title: @json(__('pharma::messages.are_you_sure')),
                text: @json(__('pharma::messages.unsaved_changes_warning')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: @json(__('pharma::messages.yes_cancel')),
                cancelButtonText: @json(__('pharma::messages.no_stay'))
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('backend.supplier-type.index') }}";
                }
            });
        });
    });
</script>
@endpush

