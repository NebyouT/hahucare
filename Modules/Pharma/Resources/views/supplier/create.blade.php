@extends('pharma::layouts.app')

@section('title')
    {{ __($isEdit ? $edit_module_title : $module_title) }}
@endsection

@section('content')
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            <h4 class="mb-4">
                {{ $supplier->exists ? __('pharma::messages.edit_supplier') : __('pharma::messages.add_supplier') }}</h4>
        </div>
        <x-slot name="toolbar">
            <div class="d-flex justify-content-end">
                <a href="{{ route('backend.suppliers.index') }}" class="btn btn-primary" data-type="ajax"
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
                        <form id="supplier-form" method="POST"
                            action="{{ $supplier->exists ? route('backend.suppliers.update', $supplier->id) : route('backend.suppliers.store') }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf @if ($supplier->exists)
                                @method('PUT')
                                <input type="hidden" id="original_email" value="{{ $supplier->email }}">
                                <input type="hidden" id="original_contact_number" value="{{ $supplier->contact_number }}">
                            @endif
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('pharma::messages.image') }}</label>
                                        <div class="image-upload-container">
                                            <div class="image-upload-box"
                                                onclick="document.getElementById('supplier_image').click();">
                                                @if (isset($imageUrl))
                                                    <img src="{{ $imageUrl }}" alt="{{ __('pharma::messages.image') }}"
                                                        id="image-preview" class="avatar-150 object-cover rounded">
                                                @else
                                                    <div class="upload-placeholder text-body fw-medium text-center">
                                                        {{ __('pharma::messages.drop_files_here_or') }}
                                                    </div>
                                                    <img src="" alt="{{ __('pharma::messages.image') }}"
                                                        id="image-preview" class="avatar-150 object-cover rounded"
                                                        style="opacity: 0; display: none;">
                                                @endif

                                                <label for="supplier_image" class="text-primary fw-medium cursor-pointer">
                                                    {{ __('pharma::messages.browse_files') }}
                                                </label>
                                                <input type="file" name="supplier_image" id="supplier_image"
                                                    class="avatar-150 object-cover rounded" accept="image/*"
                                                    style="opacity: 0; display: none;">
                                            </div>
                                        </div>
                                        <div id="image-error" class="text-danger"
                                            style="font-size: 13px; margin-top: 5px; display: none;"></div>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="first_name" class="form-label">
                                                {{ __('pharma::messages.first_name') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <input type="text" name="first_name" id="first_name" class="form-control"
                                                required placeholder="{{ __('pharma::messages.example_first_name') }}"
                                                value="{{ old('first_name', $supplier->first_name ?? '') }}"
                                                oninvalid="this.setCustomValidity(@json(__('messages.first_name_required')))"
                                                oninput="this.setCustomValidity('')">
                                            @error('first_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="last_name" class="form-label">
                                                {{ __('pharma::messages.last_name') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <input type="text" name="last_name" id="last_name" class="form-control"
                                                required placeholder="{{ __('pharma::messages.example_last_name') }}"
                                                value="{{ old('last_name', $supplier->last_name ?? '') }}"
                                                oninvalid="this.setCustomValidity(@json(__('messages.last_name_required')))"
                                                oninput="this.setCustomValidity('')">
                                            @error('last_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="email" class="form-label">
                                                {{ __('pharma::messages.email') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                required placeholder="{{ __('pharma::messages.example_email') }}"
                                                value="{{ old('email', $supplier->email ?? '') }}"
                                                oninvalid="this.setCustomValidity(@json(__('messages.email_required')))"
                                                oninput="this.setCustomValidity('')">
                                            @error('email')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div class="heading-box d-flex align-items-start justify-content-between w-100">
                                                <label for="contact_number" class="form-label">
                                                    {{ __('pharma::messages.contact_number') }}
                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>
                                            </div>
                                            <input type="tel" name="contact_number" id="contact_number"
                                                value="{{ old('contact_number', isset($supplier) ? str_replace(' ', '', $supplier->contact_number) : '') }}"
                                                class="form-control" required
                                                oninvalid="this.setCustomValidity(@json(__('messages.contact_number_required')))"
                                                oninput="this.setCustomValidity('')">

                                            <input type="hidden" id="original_contact_number"
                                                value="{{ isset($supplier) ? str_replace(' ', '', $supplier->contact_number) : '' }}">

                                            @error('contact_number')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6 custom-select-input">
                                            <label for="supplier_type" class="form-label w-100">
                                                {{ __('pharma::messages.supplier_type') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <select id="supplier_type" name="supplier_type" class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.select_supplier_type') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier_type']) }}"
                                                data-ajax--cache="true" required
                                                oninvalid="this.setCustomValidity(@json(__('messages.supplier_type_required')))"
                                                oninput="this.setCustomValidity('')">
                                                @if (old('supplier_type') || ($supplier->supplier_type_id ?? false))
                                                    @php
                                                        $selectedType = \Modules\Pharma\Models\SupplierType::find(
                                                            old('supplier_type', $supplier->supplier_type_id ?? null),
                                                    ); @endphp @if ($selectedType)
                                                        <option value="{{ $selectedType->id }}" selected>
                                                            {{ $selectedType->name }}
                                                        </option>
                                                    @endif
                                                @endif
                                            </select>
                                            @error('supplier_type')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="payment_terms" class="form-label">
                                                {{ __('pharma::messages.payment_terms') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <input type="text" name="payment_terms" id="payment_terms"
                                                class="form-control" required
                                                placeholder="{{ __('pharma::messages.example_payment_terms') }}"
                                                value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}"
                                                inputmode="numeric" pattern="[0-9]*" maxlength="10"
                                                oninput="this.value=this.value.replace(/[^0-9]/g,''); this.setCustomValidity('');"
                                                oninvalid="this.setCustomValidity(@json(__('messages.payment_terms_required')))" />

                                            @error('payment_terms')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="row">
                                        @if (auth()->user()->hasRole(['admin', 'demo_admin', 'vendor']))
                                            <div class="form-group col-lg-4 col-md-6">
                                                <label for="pharma" class="form-label">
                                                    {{ __('pharma::messages.pharma') }}
                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>
                                                <select id="pharma" name="pharma" class="form-control select2"
                                                    data-placeholder="{{ __('pharma::messages.select_pharma') }}"
                                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_id']) }}"
                                                    data-ajax--cache="true" required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.pharma_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                    @if (old('pharma') || ($supplier->pharma_id ?? false))
                                                        @php
                                                        $selectedPharma = \App\Models\User::find(
                                                                old('pharma', $supplier->pharma_id ?? null),
                                                            );
                                                        @endphp @if ($selectedPharma)
                                                            <option value="{{ $selectedPharma->id }}" selected>
                                                                {{ $selectedPharma->first_name . ' ' . $selectedPharma->last_name }}
                                                            </option>
                                                        @endif
                                                    @endif
                                                </select>
                                                @error('pharma')
                                                    <span class="text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror
                                            </div>
                                        @endif
                                        <div class="form-group col-lg-4 col-md-6">
                                            <label for="status" class="form-label">
                                                {{ __('pharma::messages.status') }}
                                                <span class="text-danger">
                                                    *
                                                </span>
                                            </label>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="status" value="0">
                                                <input type="checkbox" name="status" id="status"
                                                    class="form-check-input" value="1"
                                                    {{ old('status', $supplier->status ?? 1) ? 'checked' : '' }}>
                                            </div>
                                            @error('status')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-3" id="form-buttons">
                            <button type="button" class="btn btn-white px-4" id="cancel-btn">
                                {{ __('pharma::messages.cancel') }}
                            </button>
                            <button type="button" id="save-btn" class="btn btn-secondary">
                                <span id="save-btn-text">
                                    {{ $supplier->exists ? __('messages.update') : __('pharma::messages.save') }}
                                </span>
                                <span id="save-btn-loader" class="spinner-border spinner-border-sm ms-2 d-none"
                                    role="status" aria-hidden="true">
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3"
            style="z-index: 9999; min-width: 300px;">
            <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .styled-dropzone {
            border: 1px solid #e5e7eb;
            background-color: #f8f9fa;
            /* Light gray background */
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        . :hover {
            background-color: #f1f5f9;
            /* Slight hover effect */
        }

        .styled-dropzone.dropzone-hover {
            background-color: #e0f2fe;
            border-color: #38bdf8;
        }

        .browse-link {
            color: #ef4444;
            text-decoration: underline;
            cursor: pointer;
        }

        #dropzone-text {
            font-size: 1rem;
        }

        #image-preview {
            max-height: 140px;
            object-fit: cover;
        }
    </style>
@endpush

@push('after-scripts')
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
    <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').each(function() {
                    const $el = $(this);
                    const placeholder = $el.data('placeholder') || '';
                    $el.select2({
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: !$el.prop('multiple') && placeholder !== ''
                    });
                });
            }

            const phoneInputField = document.querySelector("#contact_number");
            const $btn = $('#save-btn');
            const $loader = $('#save-btn-loader');

            if (phoneInputField.value) {
                phoneInputField.value = phoneInputField.value.replace(/\s+/g, '');
            }

            const iti = window.intlTelInput(phoneInputField, {
                initialCountry: "in",
                separateDialCode: true,
                utilsScript: "{{ asset('vendor/intl-tel-input/js/utils.js') }}"
            });

            if (phoneInputField.value) {
                iti.setNumber(phoneInputField.value);
            }

            const itiContainer = phoneInputField.closest('.iti');
            if (itiContainer) {
                itiContainer.classList.add('w-100');
            }

            function showClientValidationErrors(formEl) {
                formEl.querySelectorAll(':invalid').forEach(function(el) {
                    const $input = $(el);
                    const $group = $input.closest('.form-group');
                    const label = $group.find('label').first().text().trim();
                    const msg = el.validationMessage || `${label} is required.`;

                    if ($group.find('.text-danger').length === 0) {
                        $('<div class="text-danger">' + msg + '</div>')
                            .insertAfter($group.children().last());
                    }
                });
            }

            const $form = $('#supplier-form');


            $form.on('submit', function(event) {
                event.preventDefault();

                const formEl = $form[0];
                $form.find('.text-danger').remove();

                if (!formEl.checkValidity()) {
                    showClientValidationErrors(formEl);
                    $btn.prop('disabled', false);
                    $loader.addClass('d-none');
                    return false;
                }

                if (!iti.isValidNumber()) {
                    $btn.prop('disabled', false);
                    $loader.addClass('d-none');
                    $('#contact_number').after(
                        '<span class="text-danger contact-exists-error">Contact Number is Invalid.</span>'
                        );
                    return false;
                }

                const dialCode = iti.getSelectedCountryData().dialCode;
                const fullNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                const nationalNumber = fullNumber.replace(`+${dialCode}`, '').replace(/^0+/, '');
                phoneInputField.value = `+${dialCode} ${nationalNumber}`;
                formEl.submit();
            });

            $btn.on('click', function(e) {
                e.preventDefault();
                $btn.prop('disabled', true);
                $loader.removeClass('d-none');
                $form.submit();
            });


            $('#contact_number').on('input', function() {
                $(this).closest('.form-group').find('.contact-exists-error').remove();
            });

            $('#contact_number').on('blur', function() {
                const val = $(this).val().replace(/\D/g, '');
                const originalContact = $('#original_contact_number').val()?.replace(/\D/g, '') ?? '';
                const errorSpan = $(this).closest('.form-group').find('.contact-exists-error');
                errorSpan.remove();

                if (val && val !== originalContact) {
                    $.ajax({
                        url: "{{ route('backend.suppliers.check-contact') }}",
                        type: "POST",
                        data: {
                            contact_number: val,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.exists) {
                                $btn.prop('disabled', false);
                                $loader.addClass('d-none');
                                $('#contact_number').after(
                                    '<span class="text-danger contact-exists-error">Contact Number already exists</span>'
                                    );
                            }
                        }
                    });
                }
            });
        });

        $('#email').on('blur', function() {
            var email = $(this).val();
            var errorSpan = $(this).closest('.form-group').find('.email-exists-error');
            var formatErrorSpan = $(this).closest('.form-group').find('.email-format-error');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var originalEmail = $('#original_email').val();


            errorSpan.remove();
            formatErrorSpan.remove();

            if (email && !emailPattern.test(email)) {

                $(this).after('<span class="text-danger email-format-error">Email format Invalid</span>');
                return;
            }


            if (email && (!originalEmail || email !== originalEmail)) {
                $.ajax({
                    url: "{{ route('backend.suppliers.check-email') }}",
                    type: "POST",
                    data: {
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.exists) {
                            $('#email').after(
                                '<span class="text-danger email-exists-error">Email ID already exists</span>'
                                );
                        }
                    }
                });
            }
        });

        $('#email').on('input', function() {

            var errorSpan = $(this).closest('.form-group').find('.email-exists-error');
            var formatErrorSpan = $(this).closest('.form-group').find('.email-format-error');
            errorSpan.remove();
            formatErrorSpan.remove();
        });



        $(document).ready(function() {
            var toastEl = document.querySelector('.toast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    delay: 3000
                });
                toast.show();
            }
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
                    window.location.href = "{{ route('backend.suppliers.index') }}";
                }
            });
        });




        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result).css({
                        display: 'block',
                        opacity: 1
                    });
                    $('.upload-placeholder').hide();
                };
                reader.readAsDataURL(file);
            }
        }

        $('#supplier_image').on('change', function() {
            previewImage(this);
        });

        let isImageValid = true;

        document.getElementById('supplier_image').addEventListener('change', function(e) {
            const file = this.files[0];
            const errorDiv = document.getElementById('image-error');
            const previewImg = document.getElementById('image-preview');
            const allowedExtensions = ['image/jpeg', 'image/jpg', 'image/png'];

            if (file) {
                if (!allowedExtensions.includes(file.type)) {
                    errorDiv.textContent = "Only JPG, JPEG, and PNG images are allowed.";
                    errorDiv.style.display = 'block';
                    previewImg.style.display = 'none';
                    isImageValid = false;
                    return;
                }

                errorDiv.style.display = 'none';
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                isImageValid = true;
                errorDiv.style.display = 'none';
            }
        });
    </script>
@endpush
