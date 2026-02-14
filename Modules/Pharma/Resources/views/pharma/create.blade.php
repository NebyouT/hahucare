@extends('pharma::layouts.app')
@section('title')
    {{ __($isEdit ? $edit_module_title : $module_title) }}
@endsection

@section('content')
    <div class="container-fluid">

        <h4 class="mb-4">
            @if (!empty($isEdit))
                {{ __('pharma::messages.edit_pharma') }}
            @else
                {{ __('pharma::messages.add_pharma') }}
            @endif
        </h4>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST"
                            action="{{ isset($pharmaDetail) ? route('backend.pharma.update', $pharmaDetail->id) : route('backend.pharma.store') }}"
                            id="pharma-form" enctype="multipart/form-data" novalidate>
                            @csrf

                            @if (isset($pharmaDetail))
                                @method('PUT')
                            @endif
                            <div class="row">
                                <!-- Image Upload Section -->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('pharma::messages.image') }}</label>
                                        <div class="image-upload-container">
                                            <div class="image-upload-box"
                                                onclick="document.getElementById('profile_image').click();">
                                                @if (isset($pharmaDetail) && $pharmaDetail->profile_image)
                                                    <img src="{{ $pharmaDetail->profile_image }}"
                                                        alt="{{ __('pharma::messages.image') }}" id="image-preview"
                                                        class="avatar-150 object-cover rounded">
                                                @else
                                                    <div class="upload-placeholder text-body fw-medium text-center">
                                                        {{ __('pharma::messages.drop_files_here_or') }}
                                                    </div>
                                                    <img src="" alt="{{ __('pharma::messages.image') }}"
                                                        id="image-preview" class="avatar-150 object-cover rounded"
                                                        style="opacity: 0; display: none;">
                                                @endif

                                                <label for="profile_image" class="text-primary fw-medium cursor-pointer">
                                                    {{ __('pharma::messages.browse_files') }}
                                                </label>
                                                <input type="file" name="profile_image" id="profile_image"
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
                                        <!-- first name -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.first_name') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="first_name"
                                                    placeholder="{{ __('pharma::messages.example_first_name') }}"
                                                    value="{{ old('first_name', $pharmaDetail->first_name ?? '') }}"
                                                    required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.first_name_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                @error('first_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Last Name -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.last_name') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="last_name"
                                                    placeholder="{{ __('pharma::messages.example_last_name') }}"
                                                    value="{{ old('last_name', $pharmaDetail->last_name ?? '') }}" required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.last_name_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                @error('last_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Email -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.email') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email"
                                                    placeholder="{{ __('pharma::messages.example_email') }}"
                                                    value="{{ old('email', $pharmaDetail->email ?? '') }}"
                                                    data-original="{{ old('email', $pharmaDetail->email ?? '') }}" required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.email_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Contact Number -->
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label
                                                    class="form-label d-block">{{ __('pharma::messages.contact_number') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="tel" name="contact_number" id="contact_number"
                                                    value="{{ old('contact_number', isset($pharmaDetail) ? $pharmaDetail->mobile : '') }}"
                                                    class="form-control" required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.contact_number_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                <input type="hidden" id="original_contact_number"
                                                    value="{{ isset($pharmaDetail) ? $pharmaDetail->mobile : '' }}">
                                                @error('contact_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Password -->
                                        @if (!isset($pharmaDetail))
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">{{ __('pharma::messages.password') }}<span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" name="password"
                                                            placeholder="12345" required
                                                            oninvalid="this.setCustomValidity(@json(__('messages.password_required')))"
                                                            oninput="this.setCustomValidity('')">
                                                        <span class="input-group-text"
                                                            onclick="togglePassword('password')">
                                                            <i class="ph ph-eye" id="password-eye"></i>
                                                        </span>
                                                        @error('password')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Confirm Password -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label
                                                        class="form-label">{{ __('pharma::messages.confirm_password') }}<span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            name="password_confirmation" placeholder="12345" required
                                                            oninvalid="this.setCustomValidity(@json(__('messages.confirm_password_required')))"
                                                            oninput="this.setCustomValidity('')">
                                                        <span class="input-group-text"
                                                            onclick="togglePassword('password_confirmation')">
                                                            <i class="ph ph-eye" id="password_confirmation-eye"></i>
                                                        </span>
                                                        @error('password_confirmation')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <!-- DOB -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label"> {{ __('pharma::messages.dob') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" id="dob" class="form-control dob-datepicker"
                                                    name="dob"
                                                    placeholder="{{ __('messages.select_date_of_birth') }}"
                                                    value="{{ old('date_of_birth', $pharmaDetail->date_of_birth ?? '') }}"
                                                    required
                                                    oninvalid="this.setCustomValidity(@json(__('messages.date_of_birth_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                @error('dob')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.address') }} </label>
                                                <input type="text" class="form-control" name="address"
                                                    placeholder="{{ __('pharma::messages.enter_address') }}"
                                                    value="{{ old('address', $pharmaDetail->address ?? '') }}">
                                            </div>
                                        </div>

                                        <!-- Clinic -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.clinic') }}<span
                                                        class="text-danger">*</span></label>
                                                <select id="clinic" name="clinic" data-filter="select"
                                                    class="form-control select2"
                                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinic_name']) }}"
                                                    data-ajax--cache="true" width="100%" required
                                                    data-placeholder="{{ __('pharma::messages.select_clinic') }}"
                                                    oninvalid="this.setCustomValidity(@json(__('messages.clinic_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                    @if (isset($pharmaDetail->id))
                                                        <option value="{{ $pharmaDetail->clinic->id }}" selected="">
                                                            {{ $pharmaDetail->clinic->name }}</option>
                                                    @else
                                                        <option value="">
                                                            {{ __('pharma::messages.select_clinic') }}</option>
                                                    @endif
                                                </select>
                                                @error('clinic')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Gender -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">{{ __('pharma::messages.select_gender') }}<span
                                                        class="text-danger">*</span></label>
                                                @php
                                                    $selectedGender = old('gender', $pharmaDetail->gender ?? '');
                                                @endphp

                                                <select class="form-select select2" name="gender" required
                                                    data-placeholder="{{ __('pharma::messages.select_gender') }}"
                                                    oninvalid="this.setCustomValidity(@json(__('messages.gender_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                    <option value="">{{ __('pharma::messages.select_gender') }}
                                                    </option>
                                                    <option value="male"
                                                        {{ $selectedGender == 'male' ? 'selected' : '' }}>
                                                        {{ __('pharma::messages.male') }}</option>
                                                    <option value="female"
                                                        {{ $selectedGender == 'female' ? 'selected' : '' }}>
                                                        {{ __('pharma::messages.female') }}</option>
                                                    <option value="other"
                                                        {{ $selectedGender == 'other' ? 'selected' : '' }}>
                                                        {{ __('pharma::messages.other') }}</option>
                                                </select>
                                                @error('gender')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- commission -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.commission') }}<span
                                                        class="text-danger">*</span></label>
                                                <select id="pharma-commission" name="pharma_commission[]"
                                                    class="form-control select2"
                                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_commission']) }}"
                                                    data-ajax--cache="true" multiple required
                                                    data-placeholder="{{ __('messages.select_commission') }}"
                                                    oninvalid="this.setCustomValidity(@json(__('messages.commission_required')))"
                                                    oninput="this.setCustomValidity('')">
                                                    <option></option> {{-- Empty option for placeholder --}}

                                                    @if (isset($pharmaDetail))
                                                        @foreach ($pharmaDetail->commissionData as $commissionItem)
                                                            @php $commission = $commissionItem->mainCommission; @endphp
                                                            @if ($commission)
                                                                <option value="{{ $commission->id }}" selected>
                                                                    {{ $commission->title }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('pharma_commission')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('pharma::messages.status') }}<span
                                                        class="text-danger">*</span></label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="status-switch"
                                                        name="status" value="1" checked>
                                                    <label class="form-check-label" for="status-switch"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-5" id="form-buttons">
                    <button type="button" class="btn btn btn-white"
                        id="cancel-btn">{{ __('pharma::messages.cancel') }}</button>
                    <button type="button" id="save-btn" class="btn btn-secondary">
                        <span id="save-btn-text">{{ __('pharma::messages.save') }}</span>
                        <span id="save-btn-loader" class="spinner-border spinner-border-sm ms-2" style="display: none;"
                            role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('after-scripts')
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
    <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const phoneInputField = document.querySelector("#contact_number");
            const $btn = $('#save-btn');
            const $loader = $('#save-btn-loader');

            const iti = window.intlTelInput(phoneInputField, {
                initialCountry: "auto",
                separateDialCode: true,
                nationalMode: false,
                geoIpLookup: function(callback) {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("IN"));
                },
                utilsScript: "{{ asset('vendor/intl-tel-input/js/utils.js') }}"
            });

            const existingNumber = @json(old('contact_number', $pharma->contact_number ?? ''));
            if (existingNumber) {
                iti.setNumber(existingNumber);
            }

            const form = phoneInputField.closest("form");
            form.addEventListener("submit", function() {
                var iti = window.intlTelInputGlobals.getInstance(document.querySelector("#contact_number"));
                var number = iti.getNumber();

                var countryData = iti.getSelectedCountryData();
                var nationalNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\s+/g,
                    '');
                var formattedNumber = `+${countryData.dialCode} ${nationalNumber}`;
                $('#contact_number').val(formattedNumber);
            });
        });
    </script>
    <link rel="stylesheet" href="{{ asset('vendor/font-awesome/css/all.min.css') }}">
    <script>
        const csrfToken = '{{ csrf_token() }}';

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

        $('#profile_image').on('change', function() {
            previewImage(this);
        });

        document.addEventListener("DOMContentLoaded", function() {
            flatpickr(".dob-datepicker", {
                dateFormat: "Y-m-d",
                allowInput: true,
                maxDate: "today"
            });
        });

        function togglePassword(fieldName) {
            const $input = $(`input[name="${fieldName}"]`);
            const $eye = $(`#${fieldName}-eye`);
            const isHidden = $input.attr('type') === 'password';
            $input.attr('type', isHidden ? 'text' : 'password');
            $eye.toggleClass('ph-eye ph-eye-slash');
        }

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
            const $email = $('input[name="email"]');
            const $contact = $('input[name="contact_number"]');
            const $password = $('input[name="password"]');
            const $confirmPassword = $('input[name="password_confirmation"]');
            const $loader = $('#save-btn-loader');
            const id = $('input[name="id"]').val() || null;


            function showError($input, message) {
                const $group = $input.closest('.form-group');
                $group.find('.text-danger').remove();
                $('<div class="text-danger">' + message + '</div>').insertAfter($group.children().last());
            }

            function removeError($input) {
                $input.closest('.form-group').find('.text-danger').remove();
            }

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            $email.on('input', debounce(function() {
                const val = $(this).val();
                const original = $(this).data('original') || '';
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!regex.test(val)) {
                    showError($email, 'Please enter a valid email address.');
                    return;
                }

                if (val === original) {
                    removeError($email);
                    return;
                }

                $.post("{{ route('check-email-exists') }}", {
                    _token: csrfToken,
                    email: val,
                    id: id
                }).then(function(res) {
                    console.log(res.exists)
                    res.exists ? showError($email, 'Email already exists.') : removeError(
                        $email);
                });
            }, 500));

            $contact.on('input blur', debounce(function() {
                const iti = window.intlTelInputGlobals.getInstance($contact[0]);
                const val = $contact.val().trim();
                const original = ($contact.data('original') || '').trim();
                let isValid = true;

                if (!val) {
                    showError($contact, 'Contact number is required.');
                    isValid = false;
                    return isValid;
                }

                if (!iti.isValidNumber()) {
                    showError($contact, 'Please enter a valid phone number.');
                    isValid = false;
                    return isValid;
                }

                const formatted = iti.getNumber();
                const originalFormatted = original;

                if (formatted === originalFormatted) {
                    removeError($contact);
                    isValid = true;
                    return isValid;
                }

                $.post("{{ route('check-contact-exists') }}", {
                    _token: csrfToken,
                    contact_number: formatted,
                    id: id
                }).then(function(res) {
                    console.log(res.exists);
                    res.exists ? showError($contact, 'Contact number already exists.') :
                        removeError($contact);
                }).fail(function() {
                    showError($contact, 'Could not verify contact number. Try again.');
                });
            }, 400));

            function checkPasswordsMatch() {
                if (!$password.length || !$confirmPassword.length) {
                    return true;
                }

                const pass = $password.val() || '';
                const confirm = $confirmPassword.val() || '';

                removeError($password);
                removeError($confirmPassword);

                let isValid = true;

                if (pass.length < 8 || pass.length > 14) {
                    showError($password, 'Password length should be 8 to 14 characters.');
                    isValid = false;
                }

                if (pass && confirm && pass !== confirm) {
                    showError($confirmPassword, 'Passwords do not match.');
                    isValid = false;
                }

                return isValid;
            }

            $password.on('input blur', checkPasswordsMatch);
            $confirmPassword.on('input blur', checkPasswordsMatch);

            $('#cancel-btn').on('click', function() {
                Swal.fire({
                    title: '{{ __('pharma::messages.are_you_sure') }}',
                    text: '{{ __('pharma::messages.unsaved_changes_warning') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('pharma::messages.yes_cancel') }}',
                    cancelButtonText: '{{ __('pharma::messages.no_stay') }}',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,

                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('backend.pharma.index') }}";
                    }
                });
            });

            let isImageValid = true;

            document.getElementById('profile_image').addEventListener('change', function(e) {
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


            $('#save-btn').on('click', function(e) {
                e.preventDefault();

                const form = $('#pharma-form').get(0);
                const $btn = $(this);
                $('.text-danger').not('#image-error').remove();

                if (!isImageValid) {
                    $('#image-error').show();
                    return false;
                }
                $btn.prop('disabled', true);
                $loader.show();

                if (!form.checkValidity()) {
                    showClientValidationErrors(form);
                    $btn.prop('disabled', false);
                    $loader.hide();
                    return false;
                }

                const iti = window.intlTelInputGlobals.getInstance($contact[0]);
                const contactVal = iti.getNumber(); // Full number with intl code
                const originalContact = ($contact.data('original') || '').replace(/\D/g, '');

                if (!iti.isValidNumber()) {
                    showError($contact, 'Please enter a valid phone number.');
                    $btn.prop('disabled', false);
                    $loader.hide();
                    return false;
                }

                const emailVal = ($email.val() || '').trim().toLowerCase();
                const originalEmail = ($email.data('original') || '').trim().toLowerCase();



                if (!checkPasswordsMatch()) {
                    $btn.prop('disabled', false);
                    $loader.hide();
                    return false;
                }

                const emailChanged = emailVal !== originalEmail;
                const contactChanged = contactVal.replace(/\D/g, '') !== originalContact;

                const emailCheck = emailChanged ?
                    $.post("{{ route('check-email-exists') }}", {
                        _token: csrfToken,
                        email: emailVal,
                        id
                    }) :
                    $.Deferred().resolve({
                        exists: false
                    });

                const contactCheck = contactChanged ?
                    $.post("{{ route('check-contact-exists') }}", {
                        _token: csrfToken,
                        contact_number: contactVal,
                        id
                    }) :
                    $.Deferred().resolve({
                        exists: false
                    });

                $.when(emailCheck, contactCheck)
                    .then(function(emailRes, contactRes) {
                        let hasError = false;
                        const emailResponse = emailChanged ? emailRes[0] : emailRes;
                        const contactResponse = contactChanged ? contactRes[0] : contactRes;

                        if (emailResponse.exists) {
                            showError($email, 'Email already exists.');
                            hasError = true;
                        }
                        if (contactResponse.exists) {
                            showError($contact, 'Contact number already exists.');
                            hasError = true;
                        }

                        if (hasError) {
                            $btn.prop('disabled', false);
                            $loader.hide();
                            return false;
                        }

                        const dialCode = iti.getSelectedCountryData().dialCode;
                        const fullNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                        const nationalNumber = fullNumber.replace(`+${dialCode}`, '').replace(/^0+/, '');
                        const formatted = `+${dialCode} ${nationalNumber}`;
                        $contact.val(formatted);

                        $('#pharma-form').off('submit').submit();
                    })
                    .fail(function() {
                        alert('Server validation failed.');
                        $btn.prop('disabled', false);
                        $loader.hide();
                    });
            });

            @if ($errors->any())
                $('#save-btn').prop('disabled', false);
                $('#save-btn-loader').hide();
            @endif
        });

        function showClientValidationErrors(form) {
            form.querySelectorAll(':invalid').forEach(function(el) {
                const $input = $(el);
                const $group = $input.closest('.form-group');
                const label = $group.find('label').first().text().trim();
                const msg = el.validationMessage || `${label} is required.`;
                if ($group.find('.text-danger').length === 0) {
                    $('<div class="text-danger">' + msg + '</div>').insertAfter($group.children().last());
                }
            });
        }
    </script>
@endpush
