@extends('pharma::layouts.app')

@section('content')
    <x-backend.section-header>
        <x-slot name="toolbar">

        </x-slot>
    </x-backend.section-header>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">
            @if (!empty($isEdit) && $isEdit)
                {{ __('pharma::messages.edit_medicine') }}
            @else
                {{ __('pharma::messages.add_medicine') }}
            @endif
        </h1>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="extra-medicine-form" method="POST"
                            action="{{ $isEdit ?? false ? route('backend.prescription.update_extra_medicine', $prescription->id) : route('backend.prescription.save_extra_medicine', $patientEncounter->id) }}"
                            enctype="multipart/form-data" class="requires-validation" novalidate>
                            @csrf

                            <div class="row">
                                <div class="col-md-4 mb-3 custom-select-input">
                                    <label class="form-label">{{ __('pharma::messages.select_medicine') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="medicine_id" class="form-control select2js" id="medicine_id"
                                        data-placeholder="{{ __('pharma::messages.select_medicine_placeholder') }}"
                                        data-ajax--url="{{ route('ajax-list', ['type' => 'medicine_prescription']) }}"
                                        data-ajax--cache="true" required>

                                        @if (isset($prescription->medicine))
                                            <option value="{{ $prescription->medicine->id }}" selected>
                                                {{ $prescription->medicine->name }} -
                                                {{ $prescription->medicine->dosage ?? '-' }}
                                            </option>
                                        @endif

                                    </select>
                                    <div id="medicine-stock" class="mt-2 text-success"
                                        style="font-size: 0.9em; display:none;">
                                        {{ __('clinic.stock') }}: <span id="stock-quantity"><b>0</b></span>
                                    </div>

                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        {{ __('pharma::messages.quantity') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="quantity" id="medicine-quantity" class="form-control"
                                        value="{{ old('quantity', $prescription->quantity ?? '') }}"
                                        placeholder="{{ __('pharma::messages.eg_quantity') }}" required>
                                    <div id="quantity-error" class="text-danger mt-1" style="display: none;"></div>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('pharma::messages.days') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="duration" class="form-control"
                                        value="{{ old('duration', $prescription->duration ?? '') }}"
                                        placeholder="{{ __('pharma::messages.eg_days') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Second row -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('pharma::messages.frequency') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="frequency" class="form-control"
                                        value="{{ old('frequency', $prescription->frequency ?? '') }}"
                                        placeholder="{{ __('pharma::messages.eg_frequency') }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ __('pharma::messages.note') }}</label>
                                    <input type="text" name="instruction" class="form-control"
                                        value="{{ old('instruction', $prescription->instruction ?? '') }}"
                                        placeholder="{{ __('pharma::messages.eg_note') }}">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn btn-md btn-primary" id="submit-button">
                                        <span id="button-loader" class="spinner-border spinner-border-sm d-none"
                                            role="status" aria-hidden="true"></span>
                                        {{ __('pharma::messages.save') }}
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



@push('after-scripts')
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
    <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var baseUrl = '{{ url('/') }}';
            // Initialize Select2 for the medicine selection
            $('.select2js').select2();

            // Form submission handler
            $('#extra-medicine-form').on('submit', function(e) {
                e.preventDefault();

                let form = $(this)[0];
                if (form.checkValidity() === false) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }


                const submitButton = $('#submit-button');
                const buttonLoader = $('#button-loader');

                buttonLoader.removeClass('d-none');
                submitButton.prop('disabled', true);

                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: $(form).serialize(),
                    success: function(response) {
                        // Handle success response
                        if (response.redirect) {
                            window.location.href = response.redirect;
                            buttonLoader.addClass('d-none');
                            submitButton.prop('disabled', false);
                            window.successSnackbar(response.message);
                        } else {
                            window.errorSnackbar(response.message);
                        }
                    },
                    error: function(xhr) {
                        // Handle error response
                        buttonLoader.addClass('d-none');
                        submitButton.prop('disabled', false);
                        window.errorSnackbar(xhr.responseJSON.message ||
                            'An error occurred. Please try again.');
                    }
                });
            });


            let currentStock = 0;

            function checkStock(medicineId, callback) {
                if (!medicineId) {
                    currentStock = 0;
                    $('#medicine-stock').hide();
                    if (callback) callback();
                    return;
                }

                $.ajax({
                    url: baseUrl + '/app/prescription/medicine-stock/' + medicineId,
                    method: 'GET',
                    success: function(response) {
                        currentStock = parseInt(response.stock) || 0;
                        $('#stock-quantity').text(currentStock);
                        $('#medicine-stock').show();
                        if (callback) callback();
                    },
                    error: function() {
                        currentStock = 0;
                        $('#medicine-stock').hide();
                        if (callback) callback();
                    }
                });
            }

            // When medicine changes
            $('#medicine_id').on('change', function() {
                const medicineId = $(this).val();
                checkStock(medicineId, function() {
                    // Optional: re-validate quantity after stock update
                    $('#medicine-quantity').trigger('input');
                });
            });

            // When quantity changes
            $('#medicine-quantity').on('input', function() {
                const medicineId = $('#medicine_id').val();
                const enteredQty = parseInt($(this).val()) || 0;

                checkStock(medicineId, function() {
                    if (enteredQty > currentStock) {
                        $('#quantity-error').text(
                            `Quantity cannot exceed available stock (${currentStock}).`).show();
                    } else {
                        $('#quantity-error').hide();
                    }
                });
            });

        });
    </script>
@endpush
