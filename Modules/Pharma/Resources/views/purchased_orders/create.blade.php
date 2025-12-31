@extends('pharma::layouts.app')

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">

        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <ul class="nav nav-tabs mb-4" id="medicineTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="medicine-details-tab" data-bs-toggle="tab" data-bs-target="#medicine-details" type="button" role="tab" disabled>
                    <i class="icon ph ph-pill"></i> {{ __('pharma::messages.medicine_details') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-details-tab" data-bs-toggle="tab" data-bs-target="#inventory-details" type="button" role="tab" disabled>
                    <i class="icon ph ph-chart-line"></i> {{ __('pharma::messages.inventory_details') }}
                </button>
            </li>
        </ul>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="POST" action="{{ $medicine->exists ? route('backend.medicine.update', $medicine->id) : route('backend.medicine.store') }}" enctype="multipart/form-data" id="medicine-form">
                            @csrf
                            @if ($medicine->exists)
                                @method('PUT')
                            @endif
                            <div class="row align-items-center mb-4">
                                <div class="col">
                                    <h1 class="h3 text-gray-800 mb-0 mt-3">{{ __('pharma::messages.basic_medicine_info') }}</h1>
                                </div>
                                <div class="col-auto text-end">
                                    <button type="button" class="btn btn-primary me-2" id="back-btn" onclick="prevTab()">{{ __('messages.back') }}</button>
                                </div>
                            </div>


                            <div class="tab-content py-4">
                                {{-- Step 1: Medicine Details --}}
                                <div class="tab-pane fade show active" id="medicine-details" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.medicine_name') }}</label><span class="text-danger">*</span>
                                            <div class="input-group">
                                                <input type="text" name="name" class="form-control" placeholder="{{ __('pharma::messages.placeholder_medicine_name') }}"  value="{{ old('name', $medicine->name ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-pill"></i></span>
                                            </div>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.enter_dosege') }}</label><span class="text-danger">*</span>
                                            <div class="input-group">
                                                <input type="text" name="dosage" class="form-control" placeholder="{{ __('pharma::messages.placeholder_dosage') }}" value="{{ old('dosage', $medicine->dosage ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-drop"></i></span>
                                            </div>
                                            @error('dosage')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.category') }}</label><span class="text-danger">*</span>
                                           <select id="medicine_category_id" name="medicine_category_id" class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.select_category') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'category']) }}"
                                                data-ajax--cache="true"
                                                required>

                                                @if (old('medicine_category_id') || ($medicine->category_id ?? false))
                                                    @php
                                                        $selectedCategory = \Modules\Pharma\Models\MedicineCategory::find(
                                                            old('medicine_category_id', $medicine->category_id ?? null)
                                                        );
                                                    @endphp
                                                    @if ($selectedCategory)
                                                        <option value="{{ $selectedCategory->id }}" selected>{{ $selectedCategory->name }}</option>
                                                    @endif
                                                @endif
                                            </select>
                                            @error('medicine_category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Supplier Info --}}
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.form') }}</label><span class="text-danger">*</span>
                                            <select id="form_id" name="form_id" class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.placeholder_form') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'form']) }}"
                                                data-ajax--cache="true"
                                                required>

                                                @if (old('form_id') || ($medicine->form_id ?? false))
                                                    @php
                                                        $selectedForm = \Modules\Pharma\Models\MedicineForm::find(
                                                            old('form_id', $medicine->form_id ?? null)
                                                        );
                                                    @endphp
                                                    @if ($selectedForm)
                                                        <option value="{{ $selectedForm->id }}" selected>{{ $selectedForm->name }}</option>
                                                    @endif
                                                @endif
                                            </select>
                                            @error('form_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.note') }}</label>
                                            <div class="input-group">
                                                <input type="text" name="note" class="form-control" placeholder="{{ __('pharma::messages.placeholder_note') }}" value="{{ old('note', $medicine->note ?? '') }}">
                                                <span class="input-group-text"><i class="icon ph ph-note-pencil"></i></span>
                                            </div>
                                        </div>
                                    </div>


                                    <hr class="my-4 border-top border-2 border-gray-300 mt-3 mb-6">

                                    <h5 class="mb-4">{{ __('pharma::messages.supplier_info') }}</h5>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label>{{ __('pharma::messages.supplier') }}</label><span class="text-danger">*</span>
                                            <select id="supplier_id" name="supplier_id" class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.placeholder_supplier') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier']) }}"
                                                data-ajax--cache="true"
                                                required>

                                                @if (old('supplier_id') || ($medicine->supplier_id ?? false))
                                                    @php
                                                        $selectedSupplier = \Modules\Pharma\Models\Supplier::find(
                                                            old('supplier_id', $medicine->supplier_id ?? null)
                                                        );
                                                    @endphp
                                                    @if ($selectedSupplier)
                                                        <option value="{{ $selectedSupplier->id }}" selected>
                                                            {{ $selectedSupplier->first_name . ' ' . $selectedSupplier->last_name }}
                                                        </option>
                                                    @endif
                                                @endif
                                            </select>
                                            @error('supplier_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label>{{ __('pharma::messages.contact_number') }}</label><span class="text-danger">*</span>
                                            <input type="tel" name="contact_number" id="contact_number" class="form-control" required
                                                placeholder="{{ __('pharma::messages.placeholder_contact') }}"
                                                value="{{ old('contact_number', $medicine->contact_number ?? '') }}">
                                            @error('contact_number')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label>{{ __('pharma::messages.payment_terms') }}</label><span class="text-danger">*</span>
                                            <div class="input-group">
                                                <input type="text" name="payment_terms" class="form-control" placeholder="{{ __('pharma::messages.placeholder_payment_terms') }}" value="{{ old('payment_terms', $medicine->payment_terms ?? '') }}">
                                                <span class="input-group-text"><i class="icon ph ph-currency-circle-dollar"></i></span>
                                            </div>
                                            @error('payment_terms')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                                {{-- Step 2: Inventory Details --}}
                                <div class="tab-pane fade" id="inventory-details" role="tabpanel">
                                    <div class="row align-items-center mb-4">
                                        <div class="col">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.quantity') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="quntity" class="form-control" placeholder="{{ __('pharma::messages.placeholder_quantity') }}" value="{{ old('quntity', $medicine->quntity ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-package"></i></span>
                                            </div>
                                            @error('quntity')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.expiry_date') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input
                                                    type="date"
                                                    name="expiry_date"
                                                    class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_expiry_date') }}"
                                                    value="{{ old('expiry_date', $medicine->expiry_date ?? '') }}"
                                                    min="{{ date('Y-m-d') }}"
                                                    required
                                                >
                                                <span class="input-group-text"><i class="ph ph-calendar-dots"></i></span>
                                            </div>
                                            @error('expiry_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.re_order_level') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="re_order_level" class="form-control" placeholder="{{ __('pharma::messages.placeholder_reorder_level') }}" value="{{ old('re_order_level', $medicine->re_order_level ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-chart-line-up"></i></span>
                                            </div>
                                            @error('re_order_level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4 border-top border-2 border-gray-300 mt-3 mb-6">

                                    <h5 class="mb-4">{{ __('pharma::messages.other') }}</h5>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.select_manufacturer') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="manufacturer" id="manufacturer-select" class="form-control select2-with-add" required>
                                                    <option value="">{{ __('pharma::messages.select_manufacturer') }}</option>
                                                    @foreach($manufacturers as $manufacturer)
                                                        <option value="{{ $manufacturer->id }}" {{ old('manufacturer', $medicine->manufacturer_id ?? '') == $manufacturer->id ? 'selected' : '' }}>{{ $manufacturer->name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="input-group-text cursor-pointer" id="add-manufacturer-btn" data-bs-toggle="modal" data-bs-target="#addManufacturerModal">
                                                    <i class="icon ph ph-plus"></i>
                                                </span>
                                            </div>
                                            @error('manufacturer_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.batch_no') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="batch_no" class="form-control" placeholder="{{  __('pharma::messages.placeholder_batch_no') }}" value="{{ old('batch_no', $medicine->batch_no ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-barcode"></i></span>
                                            </div>
                                            @error('betch_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.start_serial_no') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="start_serial_no" class="form-control" placeholder="{{  __('pharma::messages.placeholder_start_serial') }}" value="{{ old('start_serial_no', $medicine->start_serial_no ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-hash"></i></span>
                                            </div>
                                            @error('start_serial_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.end_serial_no') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="end_serial_no" class="form-control" placeholder="{{  __('pharma::messages.placeholder_end_serial') }}" value="{{ old('end_serial_no', $medicine->end_serial_no ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-hash"></i></span>
                                            </div>
                                            @error('end_serial_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4 border-top border-2 border-gray-300 mt-3 mb-6">

                                    <h5 class="mb-4">{{ __('pharma::messages.pricing') }}</h5>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.purchase_price') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="purchase_price" class="form-control" placeholder="{{  __('pharma::messages.placeholder_purchase_price') }}"  value="{{ old('purchase_price', $medicine->purchase_price ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-currency-dollar"></i></span>
                                            </div>
                                            @error('purchase_price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.selling_price') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="selling_price" class="form-control" placeholder="{{  __('pharma::messages.placeholder_selling_price') }}" value="{{ old('selling_price', $medicine->selling_price ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-currency-dollar"></i></span>
                                            </div>
                                            @error('selling_price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.tax') }}</label>
                                            <div class="d-flex align-items-center justify-content-between mt-1">
                                                <label for="is_inclusive_tax" class="mb-0 cursor-pointer d-flex align-items-center justify-content-between w-100">
                                                    <span>{{ __('pharma::messages.inclusive_tax') }}</span>
                                                    <div class="form-switch">
                                                        <input type="checkbox" name="is_inclusive_tax" id="is_inclusive_tax" class="form-check-input" value="1">
                                                        <i></i>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>{{ __('pharma::messages.stock_value') }}<span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="stock_value" class="form-control" placeholder="{{  __('pharma::messages.placeholder_stock_value') }}" value="{{ old('stock_value', $medicine->stock_value ?? '') }}" required>
                                                <span class="input-group-text"><i class="icon ph ph-currency-dollar"></i></span>
                                            </div>
                                            @error('stock_value')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3" id="form-buttons">
                    <button type="button" class="btn btn-secondary me-2" id="next-btn" onclick="nextTab()">{{ __('messages.next') }}</button>

                    <button type="button" class="btn btn-white me-2 d-none" id="cancel-btn">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-secondary d-none" id="save-btn" onclick="submitForm()">{{ __('messages.save') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Manufacturer Modal -->
    <div class="modal fade" id="addManufacturerModal" tabindex="-1" aria-labelledby="addManufacturerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addManufacturerModalLabel">{{ __('pharma::messages.add_manufacturer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('pharma::messages.manufacturer_name') }}</label>
                        <input type="text" class="form-control" id="new-manufacturer-name" required>
                        <!-- Error message will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="button" class="btn btn-primary" id="save-manufacturer-btn">{{ __('messages.save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-control {
            border-radius: 4px;
        }
        .btn {
            border-radius: 4px;
            padding: 8px 16px;
        }
        <style>
            .is-invalid {
                border-color: #dc3545 !important;
            }
            .is-valid {
                border-color: #198754 !important;
            }
            .invalid-feedback {
                display: block;
                color: #dc3545;
                font-size: 0.875em;
                margin-top: 0.25rem;
            }
        </style>
    </style>
@endpush

@push('after-scripts')
<link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
<script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
<script>
$(document).ready(function() {

    const quantityInput = document.querySelector('input[name="quntity"]');
    const sellingPriceInput = document.querySelector('input[name="selling_price"]');
    const stockValueInput = document.querySelector('input[name="stock_value"]');

    $('#manufacturer-select').select2({
        tags: true,
        createTag: function(params) {
            return null;
        }
    });

    $('#new-manufacturer-name').on('input', function() {
        $('#manufacturer-error').remove();
    });

    $('#save-manufacturer-btn').click(function() {
        const name = $('#new-manufacturer-name').val();
        $('#manufacturer-error').remove();

        if (!name) {
            showError('Please enter manufacturer name');
            return;
        }

        $.ajax({
            url: '{{ route("backend.manufacturers.store") }}',
            type: 'POST',
            data: {
                name: name,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    const newOption = new Option(response.manufacturer.name, response.manufacturer.id, true, true);
                    $('#manufacturer-select').append(newOption).trigger('change');
                    $('#addManufacturerModal').modal('hide');
                    $('#new-manufacturer-name').val('');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors && response.errors.name) {
                    showError(response.errors.name[0]);
                } else {
                    showError('Error adding manufacturer. Please try again.');
                }
            }
        });
    });

    function showError(message) {
        $('#manufacturer-error').remove();
        $('#new-manufacturer-name').after(
            `<div id="manufacturer-error" class="invalid-feedback d-block mt-1 text-danger">${message}</div>`
        );
    }

    function toggleButtons(tab) {
        var $nextBtn = $('#next-btn');
        var $cancelBtn = $('#cancel-btn');
        var $saveBtn = $('#save-btn');
        var $backBtn = $('#back-btn');

        if (tab === 'medicine') {
            $nextBtn.removeClass('d-none');
            $cancelBtn.addClass('d-none');
            $saveBtn.addClass('d-none');

            $backBtn.prop('disabled', true);
        } else {
            $nextBtn.addClass('d-none');
            $cancelBtn.removeClass('d-none');
            $saveBtn.removeClass('d-none');
            $backBtn.prop('disabled', false);
        }
    }

        const phoneInputField = document.querySelector("#contact_number");
            var iti = window.intlTelInput(phoneInputField, {
                initialCountry: "in",
                separateDialCode: true,
                utilsScript: "{{ asset('vendor/intl-tel-input/js/utils.js') }}"
            });
        toggleButtons('medicine');

        $('#cancel-btn').on('click', function() {
            window.location.href = "{{ route('backend.medicine.index') }}";
        });

        $('#medicine-details-tab').on('shown.bs.tab', function() {
            $('.h3.text-gray-800').text('{{ __('pharma::messages.basic_medicine_info') }}');
        });

        $('#inventory-details-tab').on('shown.bs.tab', function() {
            $('.h3.text-gray-800').text('{{ __('pharma::messages.basic_inventory_info') }}');
        });


        const form = document.getElementById('medicine-form');

        form.setAttribute('novalidate', true);
        $('form').find('input, select').on('input', function() {
            const tabId = $(this).closest('.tab-pane').attr('id');
            validateField(this, tabId);
        });


        updateButtonVisibility('medicine-details-tab');

        function updateStockValue() {
            const quantity = parseFloat($('input[name="quntity"]').val()) || 0;
            const sellingPrice = parseFloat($('input[name="selling_price"]').val()) || 0;
            const stockValue = quantity * sellingPrice;
            $('input[name="stock_value"]').val(stockValue.toFixed(2));
        }

        $('input[name="quntity"], input[name="selling_price"]').on('input', updateStockValue);

    });

    let currentStep = 1;
    const totalSteps = 2;
    const validationRules = {
        'medicine-details': {
            medicine_name: { required: true },
            dosage: { required: true },
            medicine_category_id: { required: true },
            form_id: { required: true },
            dob: { required: true },
            supplier_id: { required: true },
            contact_number: { required: true},
            payment_terms: { required: true }
        },
        'inventory-details': {
            quntity: { required: true, min: 1 },
            expiry_date: { required: true },
            re_order_level: { required: true, min: 0 },
            manufacturer: { required: true },
            batch_no: { required: true },
            start_serial_no: { required: true },
            end_serial_no: { required: true },
            purchase_price: { required: true, min: 0 },
            selling_price: { required: true, min: 0 },
            stock_value: { required: true, min: 0 },
            tax: { required: false }
        }
    };

    function validateCurrentStep() {
        const currentTab = document.querySelector('.tab-pane.active');
        const tabId = currentTab.id;
        const fields = currentTab.querySelectorAll('input, select');
        let isValid = true;

        fields.forEach(field => {
            if (!validateField(field, tabId)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateField(field, tabId) {
        const rules = validationRules[tabId]?.[field.name];
        if (!rules) return true;

        let isValid = true;
        const value = field.value.trim();
        const errorDiv = field.parentElement.querySelector('.invalid-feedback') ||
                        document.createElement('div');

        field.classList.remove('is-invalid', 'is-valid');
        errorDiv.classList.remove('invalid-feedback');
        errorDiv.textContent = '';

        if (rules.required && !value) {
            isValid = false;
            errorDiv.textContent = 'This field is required';
        }

        if (rules.pattern && value && !rules.pattern.test(value)) {
            isValid = false;
            errorDiv.textContent = 'Please enter a valid 10-digit phone number';
        }

        if (rules.min !== undefined && value !== '') {
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue < rules.min) {
                isValid = false;
                errorDiv.textContent = `Value must be at least ${rules.min}`;
            }
        }

        if (field.type === 'date' && value) {
            const date = new Date(value);
            if (isNaN(date.getTime())) {
                isValid = false;
                errorDiv.textContent = 'Please enter a valid date';
            }
        }
        if (!isValid) {
            field.classList.add('is-invalid');
            errorDiv.classList.add('invalid-feedback');
            if (!field.parentElement.contains(errorDiv)) {
                field.parentElement.appendChild(errorDiv);
            }
        } else {
            field.classList.add('is-valid');
        }

        return isValid;
    }

    function nextTab() {
        if (validateCurrentStep()) {
            const inventoryTab = new bootstrap.Tab(document.getElementById('inventory-details-tab'));
            inventoryTab.show();
            currentStep++;
            updateButtonVisibility('inventory-details-tab');
        }
    }

    function submitForm() {
        console.log('Form submitted', validateCurrentStep());
        if (validateCurrentStep()) {
            $("#medicine-form").submit();
        }
    }

    function prevTab() {
        const medicineTab = new bootstrap.Tab(document.getElementById('medicine-details-tab'));
        medicineTab.show();
        currentStep--;
        updateButtonVisibility('medicine-details-tab');
    }

    function updateButtonVisibility(activeTabId) {
        const backBtn = document.getElementById('back-btn');
        const nextBtn = document.getElementById('next-btn');
        const saveBtn = document.getElementById('save-btn');
        const cancelBtn = document.getElementById('cancel-btn');

        if (activeTabId === 'medicine-details-tab') {
            backBtn.disabled = true;
            nextBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
        } else {
            backBtn.disabled = false;
            nextBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
        }
    }

    $('#medicineTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
        const activeTabId = $(event.target).attr('id');
        currentStep = activeTabId === 'medicine-details-tab' ? 1 : 2;
        updateButtonVisibility(activeTabId);
    });

</script>
@endpush



