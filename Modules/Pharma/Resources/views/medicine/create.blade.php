@extends('pharma::layouts.app')

@section('title')
    {{ __($isEdit ? $edit_module_title : $module_title) }}
@endsection

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">

        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <ul class="nav nav-tabs gap-3 mb-4 custom-tab-pill" id="medicineTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="medicine-details-tab" data-bs-toggle="tab"
                    data-bs-target="#medicine-details" type="button" role="tab">
                    <i class="icon ph ph-pill"></i> {{ __('pharma::messages.medicine_details') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-details-tab" data-bs-toggle="tab" data-bs-target="#inventory-details"
                    type="button" role="tab">
                    <i class="icon ph ph-chart-line"></i> {{ __('pharma::messages.inventory_details') }}
                </button>
            </li>
        </ul>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="POST"
                            action="{{ $medicine->exists ? route('backend.medicine.update', $medicine->id) : route('backend.medicine.store') }}"
                            enctype="multipart/form-data" id="medicine-form">
                            @csrf
                            @if ($medicine->exists)
                                @method('PUT')
                            @endif
                            <div class="row align-items-center mb-5">
                                <div class="col">
                                    <h6 class="basic-medical-info font-size-18">
                                        {{ __('pharma::messages.basic_medicine_info') }}
                                    </h6>
                                </div>
                                <div class="col-auto text-end">
                                    <button type="button" class="btn btn-primary me-2" id="back-btn"
                                        onclick="prevTab()">{{ __('pharma::messages.back') }}</button>
                                </div>
                            </div>


                            <div class="tab-content">
                                {{-- Step 1: Medicine Details --}}
                                <div class="tab-pane fade show active" id="medicine-details" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.medicine_name') }}</label><span
                                                class="text-danger">*</span>

                                            <div class="form-group">
                                                <input type="text" name="name" class="form-control remove-arrow"
                                                    placeholder="{{ __('pharma::messages.placeholder_medicine_name') }}"
                                                    value="{{ old('name', $medicine->name ?? '') }}" required>
                                            </div>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.enter_dosege') }}</label><span
                                                class="text-danger">*</span>
                                            <div class="form-group">
                                                <input type="text" name="dosage" class="form-control remove-arrow"
                                                    placeholder="{{ __('pharma::messages.placeholder_dosage') }}"
                                                    value="{{ old('dosage', $medicine->dosage ?? '') }}" required>
                                            </div>
                                            @error('dosage')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3 custom-select-input">
                                            <label class="form-label">{{ __('pharma::messages.category') }}</label><span
                                                class="text-danger">*</span>
                                            <select id="medicine_category_id" name="medicine_category_id"
                                                class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.select_category') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'category']) }}"
                                                data-ajax--cache="true" required>

                                                @php
                                                    $selectedCategoryId = old(
                                                        'medicine_category_id',
                                                        $medicine->category_id ?? null,
                                                    );
                                                    if (is_array($selectedCategoryId)) {
                                                        $selectedCategoryId = \Illuminate\Support\Arr::first(
                                                            $selectedCategoryId,
                                                        );
                                                    }
                                                    $selectedCategory = $selectedCategoryId
                                                        ? \Modules\Pharma\Models\MedicineCategory::find(
                                                            $selectedCategoryId,
                                                        )
                                                        : null;
                                                @endphp
                                                @if ($selectedCategory)
                                                    <option value="{{ $selectedCategory->id }}" selected>
                                                        {{ $selectedCategory->name }}
                                                    </option>
                                                @endif
                                            </select>

                                            @error('medicine_category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3 custom-select-input">
                                            <label class="form-label">{{ __('pharma::messages.form') }}</label><span
                                                class="text-danger">*</span>
                                            <select id="form_id" name="form_id" class="form-control select2"
                                                data-placeholder="{{ __('pharma::messages.placeholder_form') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'form']) }}"
                                                data-ajax--cache="true" required>

                                                @php
                                                    $selectedFormId = old('form_id', $medicine->form_id ?? null);
                                                    if (is_array($selectedFormId)) {
                                                        $selectedFormId = \Illuminate\Support\Arr::first(
                                                            $selectedFormId,
                                                        );
                                                    }
                                                    $selectedForm = $selectedFormId
                                                        ? \Modules\Pharma\Models\MedicineForm::find($selectedFormId)
                                                        : null;
                                                @endphp
                                                @if ($selectedForm)
                                                    <option value="{{ $selectedForm->id }}" selected>
                                                        {{ $selectedForm->name }}
                                                    </option>
                                                @endif
                                            </select>

                                            @error('form_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.note') }}</label>
                                            <div class="form-group">
                                                <input type="text" name="note" class="form-control remove-arrow"
                                                    placeholder="{{ __('pharma::messages.placeholder_note') }}"
                                                    value="{{ old('note', $medicine->note ?? '') }}">
                                            </div>
                                        </div>
                                    </div>



                                    {{-- Pharma Info --}}
                                    @if (auth()->user()->user_type !== 'pharma')
                                        <hr class="my-4 border-top border-2 border-gray-300 mt-3 mb-6">
                                        <h5 class="mb-4">{{ __('pharma::messages.select_pharma') }}</h5>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" for="pharma_id">
                                                    {{ __('pharma::messages.pharma') }}</label><span
                                                    class="text-danger">*</span>

                                                <select id="pharma_id" name="pharma_id" class="select2 form-control"
                                                    data-placeholder="{{ __('pharma::messages.select_pharma') }}"
                                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_id']) }}"
                                                    data-ajax--cache="true" required>
                                                    @php
                                                        $selectedPharmaId = old(
                                                            'pharma_id',
                                                            $medicine->pharma_id ?? null,
                                                        );
                                                        if (is_array($selectedPharmaId)) {
                                                            $selectedPharmaId = \Illuminate\Support\Arr::first(
                                                                $selectedPharmaId,
                                                            );
                                                        }
                                                        $selectedPharma = $selectedPharmaId
                                                            ? \App\Models\User::where('id', $selectedPharmaId)
                                                                ->where('user_type', 'pharma')
                                                                ->first()
                                                            : null;
                                                    @endphp
                                                    @if ($selectedPharma)
                                                        <option value="{{ $selectedPharma->id }}" selected>
                                                            {{ $selectedPharma->first_name }}
                                                            {{ $selectedPharma->last_name }}</option>
                                                    @endif

                                                </select>

                                                @error('pharma_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif




                                    <hr class="my-4 border-top border-2 border-gray-300 mt-3 mb-6">


                                    {{-- Supplier Info --}}
                                    <h5 class="mb-4">{{ __('pharma::messages.supplier_info') }}</h5>

                                    <div class="row">
                                        <div class="col-12 col-md-4 mb-3 custom-select-input">
                                            <label class="form-label" for="supplier_id">
                                                {{ __('pharma::messages.supplier') }}</label><span
                                                class="text-danger">*</span>

                                            <select id="supplier_id" name="supplier_id"
                                                class="select2 form-control w-100"
                                                data-placeholder="{{ __('pharma::messages.placeholder_supplier') }}"
                                                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'supplier_by_pharma']) }}"
                                                data-ajax--cache="true" data-dependent-on="#pharma_id" required>
                                                @php
                                                    $selectedSupplierId = old(
                                                        'supplier_id',
                                                        $medicine->supplier_id ?? null,
                                                    );
                                                    if (is_array($selectedSupplierId)) {
                                                        $selectedSupplierId = \Illuminate\Support\Arr::first(
                                                            $selectedSupplierId,
                                                        );
                                                    }
                                                    $selectedSupplier = $selectedSupplierId
                                                        ? \Modules\Pharma\Models\Supplier::find($selectedSupplierId)
                                                        : null;
                                                @endphp
                                                @if ($selectedSupplier)
                                                    <option value="{{ $selectedSupplier->id }}" selected>
                                                        {{ $selectedSupplier->first_name . ' ' . $selectedSupplier->last_name }}
                                                    </option>
                                                @endif
                                            </select>

                                            @error('supplier_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.contact_number') }}</label><span
                                                class="text-danger">*</span>
                                            <input type="tel" name="contact_number" id="contact_number"
                                                class="form-control bg-body" required
                                                value="{{ old('contact_number', $medicine->contact_number ?? '') }}"
                                                readonly>

                                            @error('contact_number')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-4 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.payment_terms_day') }}</label><span
                                                class="text-danger">*</span>
                                            <div class="form-group">
                                                <input type="number" name="payment_terms" class="form-control bg-body"
                                                    placeholder="{{ __('pharma::messages.placeholder_payment_terms') }}"
                                                    value="{{ old('payment_terms', $medicine->payment_terms ?? '') }}"
                                                    id="payment_terms" readonly>
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

                                    <div class="row mb-5 pb-5 border-bottom">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.batch_no') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="text" name="batch_no" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_batch_no') }}"
                                                    value="{{ old('batch_no', $medicine->batch_no ?? '') }}" required>
                                            </div>
                                            @error('betch_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.start_serial_no') }}</label>
                                            <div class="form-group">
                                                <input type="number" id="start_serial_no" name="start_serial_no"
                                                    class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_start_serial') }}"
                                                    value="{{ old('start_serial_no', $medicine->start_serial_no ?? '') }}">
                                            </div>
                                            @error('start_serial_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.end_serial_no') }}</label>
                                            <div class="form-group">
                                                <input type="number" id="end_serial_no" name="end_serial_no"
                                                    class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_end_serial') }}"
                                                    value="{{ old('end_serial_no', $medicine->end_serial_no ?? '') }}">
                                            </div>
                                            @error('end_serial_no')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.quantity') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="number" id="quantity" name="quntity" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_quantity') }}"
                                                    value="{{ old('quntity', $medicine->quntity ?? '') }}" required>
                                            </div>
                                            @error('quntity')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    </div>



                                    <h5 class="mb-4">{{ __('pharma::messages.other') }}</h5>

                                    <div class="row mb-5 pb-5 border-bottom">
                                        <div class="col-md-3 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.select_manufacturer') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-select-input">
                                                <select id="manufacturer" name="manufacturer"
                                                    class="select2 form-control"
                                                    data-placeholder="{{ __('pharma::messages.select_manufacturer') }}"
                                                    data-ajax--url="{{ route('backend.get_search_data', ['type' => 'manufacturer_by_pharma']) }}"
                                                    data-ajax--cache="true" required>

                                                    @php
                                                        $selectedManufacturerId = old(
                                                            'manufacturer',
                                                            $medicine->manufacturer_id ?? null,
                                                        );
                                                        if (is_array($selectedManufacturerId)) {
                                                            $selectedManufacturerId = \Illuminate\Support\Arr::first(
                                                                $selectedManufacturerId,
                                                            );
                                                        }
                                                        $selectedManufacturer = $selectedManufacturerId
                                                            ? \Modules\Pharma\Models\Manufacturer::find(
                                                                $selectedManufacturerId,
                                                            )
                                                            : null;
                                                    @endphp
                                                    @if ($selectedManufacturer)
                                                        <option value="{{ $selectedManufacturer->id }}" selected>
                                                            {{ $selectedManufacturer->name }}
                                                        </option>
                                                    @endif

                                                </select>
                                                <span class="input-group-text cursor-pointer" id="add-manufacturer-btn"
                                                    data-bs-toggle="modal" data-bs-target="#addManufacturerModal">
                                                    <i class="icon ph ph-plus"></i>
                                                </span>

                                            </div>
                                            @error('manufacturer_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.expiry_date') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="text" name="expiry_date" id="expiry_date"
                                                    class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_expiry_date') }}"
                                                    value="{{ old('expiry_date', $medicine->expiry_date ?? '') }}"
                                                    autocomplete="off" required>
                                            </div>
                                            @error('expiry_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.re_order_level') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="number" name="re_order_level" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_reorder_level') }}"
                                                    value="{{ old('re_order_level', $medicine->re_order_level ?? '') }}"
                                                    required>
                                            </div>
                                            @error('re_order_level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    @php
                                        $currencySymbol = getCurrencySymbol();
                                        if (is_array($currencySymbol)) {
                                            $currencySymbol = reset($currencySymbol);
                                        }
                                    @endphp
                                    <h5 class="mb-4">{{ __('pharma::messages.pricing') }}</h5>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.purchase_price') . '(' . $currencySymbol . ')' }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="number" name="purchase_price" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_purchase_price') }}"
                                                    value="{{ old('purchase_price', $medicine->purchase_price ?? '') }}"
                                                    required>
                                            </div>
                                            @error('purchase_price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.selling_price') . '(' . $currencySymbol . ')' }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <input type="number" name="selling_price" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_selling_price') }}"
                                                    value="{{ old('selling_price', $medicine->selling_price ?? '') }}"
                                                    required>
                                            </div>
                                            @error('selling_price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">{{ __('pharma::messages.tax') }}</label>
                                            <div class="d-flex align-items-center justify-content-between mt-1">
                                                <label for="is_inclusive_tax"
                                                    class="mb-0 cursor-pointer d-flex align-items-center justify-content-between w-100">
                                                    <span>{{ __('pharma::messages.inclusive_tax') }}</span>
                                                    <div class="form-switch">
                                                        <input type="checkbox" name="is_inclusive_tax"
                                                            id="is_inclusive_tax" class="form-check-input" value="1"
                                                            {{ old('is_inclusive_tax', $medicine->is_inclusive_tax ?? false) ? 'checked' : '' }}>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label
                                                class="form-label">{{ __('pharma::messages.stock_value') . '(' . $currencySymbol . ')' }}<span
                                                    class="text-danger">*</span>
                                                <i class="fas fa-info-circle" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ __('pharma::messages.stock_value_tooltip') }}"
                                                    style="cursor: pointer; font-size: 16px; margin-left: 5px;">
                                                </i>
                                            </label>
                                            <div class="form-group">
                                                <input type="number" name="stock_value" class="form-control"
                                                    placeholder="{{ __('pharma::messages.placeholder_stock_value') }}"
                                                    value="{{ old('stock_value', $medicine->stock_value ?? '') }}"
                                                    readonly>
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

                    <button type="button" class="btn btn-secondary me-2" id="next-btn" onclick="nextTab()" disabled>
                        <span id="next-btn-loader" class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span id="next-btn-text">{{ __('pharma::messages.next') }}</span>
                    </button>

                    <button type="button" class="btn btn-white me-2 d-none"
                        id="cancel-btn">{{ __('pharma::messages.cancel') }}</button>
                    <button type="submit" class="btn btn-secondary d-none" id="save-btn" onclick="submitForm()">
                        <span id="submit-btn-loader" class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span id="submit-btn-text">{{ __('pharma::messages.save') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Manufacturer Modal -->
    <div class="modal fade" id="addManufacturerModal" tabindex="-1" aria-labelledby="addManufacturerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addManufacturerModalLabel">{{ __('pharma::messages.add_manufacturer') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('pharma::messages.manufacturer_name') }}</label>
                        <input type="text" class="form-control" id="new-manufacturer-name"
                            placeholder="{{ __('pharma::messages.add_manufacturer') }}" required>
                        <!-- Error message will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="button" class="btn btn-primary"
                        id="save-manufacturer-btn">{{ __('messages.save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
    <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <style>
        .remove-arrow {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }

        .remove-arrow::-webkit-outer-spin-button,
        .remove-arrow::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .remove-arrow[type=number] {
            -moz-appearance: textfield;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            display: none !important;
        }
    </style>
    <script>
        let iti;
        $(document).ready(function() {

            const quantityInput = document.querySelector('input[name="quntity"]');
            const sellingPriceInput = document.querySelector('input[name="selling_price"]');
            const stockValueInput = document.querySelector('input[name="stock_value"]');

            flatpickr("#expiry_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                allowInput: true
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
                    url: '{{ route('backend.manufacturers.store') }}',
                    type: 'POST',
                    data: {
                        name: name,
                        pharma_id: $('#pharma_id').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {

                        if (response.success) {
                            window.successSnackbar(response.message);
                            const $manufacturerSelect = $('#manufacturer');
                            const newOption = new Option(response.manufacturer.name, response
                                .manufacturer.id, true, true);
                            $manufacturerSelect.append(newOption).trigger('change');
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

            function initAjaxSelect($element, extraParamsCallback = null, configOverrides = {}) {
                if (!$element.length) {
                    return;
                }

                if ($element.hasClass('select2-hidden-accessible')) {
                    $element.select2('destroy');
                }

                const ajaxUrl = $element.data('ajax--url');
                const placeholder = $element.data('placeholder') || $element.attr('placeholder') || '';

                const commonConfig = $.extend(true, {
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: false,
                    minimumInputLength: 0,
                    dropdownParent: $element.closest('.modal, .offcanvas').length ? $element.closest(
                        '.modal, .offcanvas') : $(document.body)
                }, configOverrides);

                if (!ajaxUrl) {
                    $element.select2(commonConfig);
                    return;
                }

                $element.select2($.extend(true, {}, commonConfig, {
                    ajax: {
                        url: ajaxUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            const base = {
                                term: params.term || ''
                            };
                            if (typeof extraParamsCallback === 'function') {
                                return Object.assign(base, extraParamsCallback());
                            }
                            return base;
                        },
                        processResults: function(data) {
                            return data;
                        },
                        cache: true
                    }
                }));
            }

            initAjaxSelect($('#medicine_category_id'));
            initAjaxSelect($('#form_id'));
            initAjaxSelect($('#pharma_id'));

            function toggleButtons(tab) {
                var $nextBtn = $('#next-btn');
                var $cancelBtn = $('#cancel-btn');
                var $saveBtn = $('#save-btn');
                var $backBtn = $('#back-btn');

                if (tab === 'medicine') {
                    $nextBtn.removeClass('d-none');
                    $cancelBtn.addClass('d-none');
                    $saveBtn.addClass('d-none');

                } else {
                    $nextBtn.addClass('d-none');
                    $cancelBtn.removeClass('d-none');
                    $saveBtn.removeClass('d-none');

                    $backBtn.removeClass('d-none');
                }
            }

            function getSupplierPhoneAndTerms(supplierId) {
                $.ajax({
                    url: '{{ route('backend.suppliers.supplier-info') }}',
                    type: 'GET',
                    data: {
                        supplierId: supplierId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            iti.setNumber(response.data.contact_number);
                            $("#payment_terms").val(response.data.payment_terms);
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
            }


            const phoneInputField = document.querySelector("#contact_number");
            iti = window.intlTelInput(phoneInputField, {
                initialCountry: "in",
                separateDialCode: true,
                utilsScript: "{{ asset('vendor/intl-tel-input/js/utils.js') }}"
            });


            toggleButtons('medicine');

            $('#cancel-btn').on('click', function() {
                window.location.href = "{{ route('backend.medicine.index') }}";
            });

            $('#medicine-details-tab').on('shown.bs.tab', function() {
                $('.basic-medical-info').text('{{ __('pharma::messages.basic_medicine_info') }}');
            });

            $('#inventory-details-tab').on('shown.bs.tab', function() {
                $('.basic-medical-info').text('{{ __('pharma::messages.basic_inventory_info') }}');
            });


            const form = document.getElementById('medicine-form');

            // Prevent HTML5 validation
            form.setAttribute('novalidate', true);

            // Handle form submission


            // Add input event listeners for real-time validation
            $('form').find('input, select').on('input change', function() {
                const tabId = $(this).closest('.tab-pane').attr('id');
                validateField(this, tabId);
                updateNextButtonState();
            });


            let isPharmaUser = {{ auth()->user()->hasRole('pharma') ? 'true' : 'false' }};
            let pharmaId = isPharmaUser ? {{ auth()->id() }} : null;

            function initSupplierSelect() {
                const $supplier = $('#supplier_id');
                if (!$supplier.length) {
                    return;
                }

                const currentValue = $supplier.val();

                initAjaxSelect($supplier, () => ({
                    pharma_id: getCurrentPharmaId() || ''
                }));

                if (currentValue) {
                    $supplier.val(currentValue).trigger('change');
                }
            }

            function initManufacturerSelect() {
                const $manufacturer = $('#manufacturer');
                if (!$manufacturer.length) {
                    return;
                }

                const currentValue = $manufacturer.val();

                initAjaxSelect($manufacturer, () => ({
                    pharma_id: getCurrentPharmaId() || ''
                }));

                if (currentValue) {
                    $manufacturer.val(currentValue).trigger('change');
                }
            }

            function getCurrentPharmaId() {
                if (isPharmaUser) {
                    return pharmaId;
                }
                const value = $('#pharma_id').val();
                if (!value) {
                    return null;
                }
                if (Array.isArray(value)) {
                    return value[0];
                }
                return value;
            }

            initSupplierSelect();
            initManufacturerSelect();

            if (!isPharmaUser) {
                $('#pharma_id').on('change', function() {
                    pharmaId = getCurrentPharmaId();
                    $('#supplier_id').val(null).trigger('change');
                    $('#manufacturer').val(null).trigger('change');
                    initSupplierSelect();
                    initManufacturerSelect();
                });
            }



            updateButtonVisibility('medicine-details-tab');
            updateNextButtonState();

            function updateStockValue() {
                const quantity = parseFloat($('input[name="quntity"]').val()) || 0;
                const sellingPrice = parseFloat($('input[name="selling_price"]').val()) || 0;
                const stockValue = quantity * sellingPrice;
                $('input[name="stock_value"]').val(stockValue.toFixed(2));
            }

            $('input[name="quntity"], input[name="selling_price"]').on('input', updateStockValue);
            $('input[name="selling_price"]').on('input', function() {
                validateField(document.querySelector('[name="purchase_price"]'), 'inventory-details');
                validateField(document.querySelector('[name="selling_price"]'), 'inventory-details');
            });
            $('select[name="supplier_id"]').on('change', function() {
                let supplierId = $(this).val();
                if (supplierId) {
                    getSupplierPhoneAndTerms(supplierId);
                }
            });



        });



        let currentStep = 1;
        const totalSteps = 2;

        const validationRules = {
            'medicine-details': {
                name: {
                    required: true
                },
                dosage: {
                    required: true
                },
                medicine_category_id: {
                    required: true
                },
                form_id: {
                    required: true
                },
                dob: {
                    required: true
                },
                pharma_id: {
                    required: true
                },
                supplier_id: {
                    required: true
                },

            },
            'inventory-details': {
                quntity: {
                    required: true,
                    min: 1,
                    pattern: /^\d+$/
                },
                expiry_date: {
                    required: true
                },
                re_order_level: {
                    required: true,
                    min: 0,
                    pattern: /^\d+$/
                },
                manufacturer: {
                    required: true
                },
                batch_no: {
                    required: true
                },
                start_serial_no: {
                    required: true,
                    pattern: /^[0-9]+$/
                },
                end_serial_no: {
                    required: true,
                    pattern: /^[0-9]+$/
                },
                purchase_price: {
                    required: true,
                    min: 0
                },
                selling_price: {
                    required: true,
                    min: 0
                },
                stock_value: {
                    required: true,
                    min: 0
                }
            }
        };

        function areRequiredFieldsFilled(tabId) {
            const rules = validationRules[tabId];
            if (!rules) {
                return true;
            }

            return Object.keys(rules).every(fieldName => {
                const $field = $(`#${tabId} [name="${fieldName}"]`);
                if (!$field.length) {
                    return true;
                }

                if ($field.attr('type') === 'checkbox' || $field.attr('type') === 'radio') {
                    return $field.is(':checked');
                }

                const value = $field.val();

                if (Array.isArray(value)) {
                    return value.some(val => val !== null && val.toString().trim() !== '');
                }

                return value !== null && value.toString().trim() !== '';
            });
        }

        function updateNextButtonState() {
            const isMedicineTabActive = $('#medicine-details-tab').hasClass('active');

            if (!isMedicineTabActive) {
                $('#next-btn').prop('disabled', false);
                return;
            }

            const canProceed = areRequiredFieldsFilled('medicine-details');
            $('#next-btn').prop('disabled', !canProceed);
        }

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


        const fieldLabels = {
            name: 'Medicine name',
            dosage: 'Dosage',
            medicine_category_id: 'Category',
            form_id: 'Form',
            pharma_id: 'Pharma',
            supplier_id: 'Supplier',
            quntity: 'Quantity',
            expiry_date: 'Expiry date',
            re_order_level: 'Re-order level',
            selling_price: 'Selling price',
            purchase_price: 'Purchase price',
            batch_no: 'Batch number',
            start_serial_no: 'Start serial number',
            end_serial_no: 'End serial number',
            stock_value: 'Stock value',
            manufacturer: 'Manufacturer',


        };



        function validateField(field, tabId) {
            const rules = validationRules[tabId]?.[field.name];
            if (!rules) return true;

            let isValid = true;
            const value = field.value.trim();
            let errorDiv = field.parentElement.querySelector('.invalid-feedback');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.classList.add('invalid-feedback');
                field.parentElement.appendChild(errorDiv);
            }

            const label = fieldLabels[field.name] || 'This field';

            // Reset classes and text
            field.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';

            // Required
            if (rules.required && !value) {
                isValid = false;
                errorDiv.textContent = `${label} is required`;
            }

            // Pattern
            if (rules.pattern && value && !rules.pattern.test(value)) {
                isValid = false;
                if (field.name === 'contact_number') {
                    errorDiv.textContent = 'Please enter a valid 10-digit phone number';
                } else if (field.name === 'start_serial_no' || field.name === 'end_serial_no') {
                    errorDiv.textContent = 'Only digits are allowed';
                } else {
                    errorDiv.textContent = `Invalid ${label.toLowerCase()} format`;
                }
            }

            // Minimum
            if (rules.min !== undefined && value !== '') {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < rules.min) {
                    isValid = false;
                    errorDiv.textContent = `${label} must be at least ${rules.min}`;
                }
            }

            // Date
            if (field.type === 'date' && value) {
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    isValid = false;
                    errorDiv.textContent = `Please enter a valid ${label.toLowerCase()}`;
                }
            }

            // Selling price validation
            const purchaseField = document.querySelector('[name="purchase_price"]');
            const sellingField = document.querySelector('[name="selling_price"]');
            const purchaseValue = parseFloat(purchaseField?.value || 0);
            const sellingValue = parseFloat(sellingField?.value || 0);

            if (!isNaN(purchaseValue) && !isNaN(sellingValue)) {
                if (sellingValue < purchaseValue) {
                    if (field.name === 'selling_price') {
                        isValid = false;
                        errorDiv.textContent = 'Selling Price should be greater than Purchase Price';
                    } else if (field.name === 'purchase_price' && purchaseValue > sellingValue) {
                        isValid = false;
                        errorDiv.textContent = 'Purchase Price should be less than Selling Price';
                    }
                } else {
                    isValid = true;
                    errorDiv.textContent = '';
                }
            } else {
                isValid = true;
                errorDiv.textContent = '';
            }

            if (!isValid) {
                field.classList.add('is-invalid');
                errorDiv.style.display = 'block';
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                errorDiv.style.display = 'none';
            }

            return isValid;
        }
        function nextTab() {
            $('#next-btn').prop('disabled', true);
            $('#next-btn-loader').removeClass('d-none');
            setTimeout(function() {
                if (validateCurrentStep()) {
                    const inventoryTab = new bootstrap.Tab($('#inventory-details-tab'));
                    inventoryTab.show();
                    currentStep++;
                    updateButtonVisibility('inventory-details-tab');
                    $('#next-btn').prop('disabled', false);
                    $('#next-btn-loader').addClass('d-none');
                    $('#next-btn-text').removeClass('d-none');
                } else {
                    $('#next-btn').prop('disabled', false);
                    $('#next-btn-loader').addClass('d-none');
                    $('#next-btn-text').removeClass('d-none');
                }
            }, 500);

        }

        document.addEventListener('DOMContentLoaded', function() {
            const startInput = document.getElementById('start_serial_no');
            const endInput = document.getElementById('end_serial_no');
            const qtyInput = document.getElementById('quantity');

            function autoCalculateQuantity() {
                const start = parseInt(startInput.value);
                const end = parseInt(endInput.value);

                if (!isNaN(start) && !isNaN(end) && end >= start) {
                    qtyInput.value = (end - start + 1);
                }
            }

            startInput.addEventListener('input', autoCalculateQuantity);
            endInput.addEventListener('input', autoCalculateQuantity);
        });

        function submitForm() {
            $('#save-btn').prop('disabled', true);
            $('#submit-btn-loader').removeClass('d-none');
            $('#next-btn-text').addClass('d-none');
            const phoneInputField = document.querySelector("#contact_number");
            console.log('iti', iti);
            const dialCode = iti.getSelectedCountryData().dialCode;
            const nationalNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\D/g, '');
            const formattedNumber = `+${dialCode} ${nationalNumber}`;
            phoneInputField.value = formattedNumber;

            setTimeout(function() {
                if (validateCurrentStep()) {
                    $("#medicine-form").submit();
                } else {
                    // On error: enable Save button and hide loader
                    $('#save-btn').prop('disabled', false);
                    $('#submit-btn-loader').addClass('d-none');
                    $('#next-btn-text').removeClass('d-none');
                }
            }, 500);
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
                backBtn.classList.add('d-none');
                nextBtn.classList.remove('d-none');
                saveBtn.classList.add('d-none');
                cancelBtn.classList.add('d-none');
            } else {
                backBtn.classList.remove('d-none');
                nextBtn.classList.add('d-none');
                saveBtn.classList.remove('d-none');
                cancelBtn.classList.remove('d-none');
            }
        }

        $('#medicineTab button[data-bs-toggle="tab"]').on('show.bs.tab', function(event) {
            const targetTab = $(event.target).attr('id');
            const currentTab = $(event.relatedTarget).attr('id'); // The tab being switched from

            if (currentTab === 'medicine-details-tab' && targetTab === 'inventory-details-tab') {
                if (!validateCurrentStep()) {
                    event.preventDefault(); // Stop tab switch
                    const firstInvalid = $('.tab-pane.active').find('.is-invalid').first();
                    if (firstInvalid.length) {
                        $('html, body').animate({
                            scrollTop: firstInvalid.offset().top - 100
                        }, 300);
                        firstInvalid.focus();
                    }
                }
            }
        });

        $('#medicineTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
            const activeTabId = $(event.target).attr('id');
            currentStep = activeTabId === 'medicine-details-tab' ? 1 : 2;
            updateButtonVisibility(activeTabId);
            updateNextButtonState();
        });
    </script>
@endpush
