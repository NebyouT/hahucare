@extends('pharma::layouts.app')

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">

        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">
            {{ $supplier->exists ? __('pharma::messages.edit_supplier') : __('pharma::messages.add_supplier') }}
        </h1>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="supplier-form" method="POST"
                            action="{{ $supplier->exists ? route('backend.suppliers.update', $supplier->id) : route('backend.suppliers.store') }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            @if ($supplier->exists)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <!-- Image -->
                                <div class="form-group col-md-4">
                                    <label for="image" class="form-label">{{ __('pharma::messages.image') }} <span
                                            class="text-danger">*</span></label>
                                    <div id="image-dropzone" class="styled-dropzone">
                                        <p id="dropzone-text" class="text-muted m-0">
                                            {{ __('pharma::messages.drop_files_here_or') }}
                                            <span class="browse-link">{{ __('pharma::messages.browse_files') }}</span>
                                        </p>
                                        @php
                                            $imageUrl = getSingleMedia($supplier, 'supplier_image');
                                        @endphp

                                        <img id="image-preview" src="{{ $imageUrl ?: '#' }}"
                                            alt="{{ __('pharma::messages.image') }}"
                                            class="img-fluid avatar-140 avatar-rounded {{ $imageUrl ? '' : 'd-none' }} mt-2" />
                                        <input type="file" name="image" id="image" class="d-none"
                                            accept=".jpeg,.jpg,.png,.gif" />
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger mt-2 {{ $imageUrl ? '' : 'd-none' }}"
                                            id="remove-image-btn" onclick="removeImage()">
                                            {{ __('pharma::messages.remove') }}
                                        </button>
                                    </div>
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- First Name -->
                                <div class="form-group col-md-4">
                                    <label for="first_name" class="form-label">{{ __('pharma::messages.first_name') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name" class="form-control" required
                                        placeholder="{{ __('e.g. Courtney') }}"
                                        value="{{ old('first_name', $supplier->first_name ?? '') }}">
                                    @error('first_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Last Name -->
                                <div class="form-group col-md-4">
                                    <label for="last_name" class="form-label">{{ __('pharma::messages.last_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" required
                                        placeholder="{{ __('e.g. Henry') }}"
                                        value="{{ old('last_name', $supplier->last_name ?? '') }}">
                                    @error('last_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Email -->
                                <div class="form-group col-md-4 offset-md-4">
                                    <label for="email" class="form-label">{{ __('pharma::messages.email') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" required
                                        placeholder="{{ __('e.g. courtney@gmail.com') }}"
                                        value="{{ old('email', $supplier->email ?? '') }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Contact Number -->
                                <div class="form-group col-md-2">
                                    <label for="contact_number"
                                        class="form-label">{{ __('pharma::messages.contact_number') }}<span
                                            class="text-danger">*</span></label>
                                    <input type="tel" name="contact_number" id="contact_number" class="form-control"
                                        required placeholder="{{ __('e.g. 123456789') }}"
                                        value="{{ old('contact_number', $supplier->contact_number ?? '') }}">
                                    @error('contact_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Supplier Type -->
                                <div class="form-group col-md-4 offset-md-4">
                                    <label for="supplier_type"
                                        class="form-label">{{ __('pharma::messages.supplier_type') }}<span
                                            class="text-danger">*</span></label>
                                    <select id="supplier_type" name="supplier_type" class="form-control select2"
                                        data-placeholder="{{ __('messages.select_supplier_type') }}"
                                        data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier_type']) }}"
                                        data-ajax--cache="true" required>

                                        @if (old('supplier_type') || ($supplier->supplier_type_id ?? false))
                                            @php
                                                $selectedSupplierType = \Modules\Pharma\Models\SupplierType::find(
                                                    old('supplier_type', $supplier->supplier_type_id ?? null),
                                                );
                                            @endphp
                                            @if ($selectedSupplierType)
                                                <option value="{{ $selectedSupplierType->id }}" selected>
                                                    {{ $selectedSupplierType->name }}
                                                </option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('supplier_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Payment Terms -->
                                <div class="form-group col-md-4">
                                    <label for="payment_terms"
                                        class="form-label">{{ __('pharma::messages.payment_terms') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="payment_terms" id="payment_terms" class="form-control"
                                        required placeholder="{{ __('e.g. 30 days') }}"
                                        value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}">
                                    @error('payment_terms')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if (auth()->user()->hasRole(['admin', 'demo_admin']))
                                    <div class="form-group col-md-4">
                                        <label for="pharma" class="form-label">{{ __('pharma::messages.pharma') }}<span
                                                class="text-danger">*</span></label>
                                        <select id="pharma" name="pharma" class="form-control select2"
                                            data-placeholder="{{ __('messages.select_pharma') }}"
                                            data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_id']) }}"
                                            data-ajax--cache="true" required>

                                            @if (old('pharma') || ($supplier->pharma_id ?? false))
                                                @php
                                                    $selectedPharma = \App\Models\User::find(
                                                        old('pharma', $supplier->pharma_id ?? null),
                                                    );
                                                @endphp
                                                @if ($selectedPharma)
                                                    <option value="{{ $selectedPharma->id }}" selected>
                                                        {{ $selectedPharma->first_name . ' ' . $selectedPharma->last_name }}
                                                    </option>
                                                @endif
                                            @endif
                                        </select>
                                        @error('pharma')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                <div class="form-group col-md-4">
                                    <label for="status" class="form-label">{{ __('pharma::messages.status') }}<span
                                            class="text-danger">*</span></label>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="status" value="0">
                                        <input type="checkbox" name="status" id="status" class="form-check-input"
                                            value="1" {{ old('status', $supplier->status ?? 1) ? 'checked' : '' }}>
                                    </div>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn btn-md btn-primary" id="submit-button">
                                        <span id="button-loader" class="spinner-border spinner-border-sm d-none"
                                            role="status" aria-hidden="true"></span>
                                        {{ $supplier->exists ? __('Update') : __('Create') }}
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

        .styled-dropzone:hover {
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

            const phoneInputField = document.querySelector("#contact_number");
            var iti = window.intlTelInput(phoneInputField, {
                initialCountry: "in",
                separateDialCode: true,
                utilsScript: "{{ asset('vendor/intl-tel-input/js/utils.js') }}"
            });

            const form = $('#supplier-form');
            const submitButton = $('#submit-button');
            const loader = $('#button-loader');

            form.on('submit', function(event) {
                const dialCode = iti.getSelectedCountryData().dialCode;
                const nationalNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\D/g,
                    '');
                const formattedNumber = `+${dialCode} ${nationalNumber}`;

                phoneInputField.value = formattedNumber;
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

        const dropzone = document.getElementById('image-dropzone');
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('image-preview');
        const dropzoneText = document.getElementById('dropzone-text');
        const removeBtn = document.getElementById('remove-image-btn');

        dropzone.addEventListener('click', () => fileInput.click());
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dropzone-hover');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dropzone-hover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dropzone-hover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImage({
                    target: fileInput
                });
            }
        });

        fileInput.addEventListener('change', previewImage);

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                preview.src = reader.result;
                preview.classList.remove('d-none');
                dropzoneText.classList.add('d-none');
                removeBtn.classList.remove('d-none');
            };
            if (event.target.files && event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        function removeImage() {
            fileInput.value = '';
            preview.src = '#';
            preview.classList.add('d-none');
            dropzoneText.classList.remove('d-none');
            removeBtn.classList.add('d-none');
        }
    </script>
@endpush
