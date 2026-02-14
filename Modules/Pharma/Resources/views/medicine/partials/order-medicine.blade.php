<form class="requires-validation" id="orderMedicineForm" action="{{ route('backend.order-medicine.store') }}" method="POST"
    novalidate>
    @csrf
    <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">

    <div class="mb-3">
        <label for="quantity" class="form-label">{{ __('pharma::messages.quantity') }}</label><span
            class="text-danger">*</span>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1"
            placeholder="{{ __('pharma::messages.placeholder_quantity') }}" required>
        <div class="invalid-feedback">{{ __('pharma::messages.quantity_required') }}</div>
    </div>

    <div class="mb-3">
        <label for="purchase_price" class="form-label">{{ __('pharma::messages.purchase_price') }}</label>
        <input type="text" name="purchase_price" id="purchase_price" class="form-control" readonly
            value="{{ number_format($medicine->purchase_price, 2) }}">
    </div>

    <div class="mb-3">
        <label for="total_amount" class="form-label">{{ __('pharma::messages.total_amount') }}</label>
        <input type="text" name="total_amount" id="total_amount" class="form-control" readonly>
    </div>

    <div class="mb-3">
        <label for="order_status" class="form-label">{{ __('pharma::messages.order_status') }}</label><span
            class="text-danger">*</span>
        <select name="order_status" id="order_status" class="form-control select2">
            <option value="pending">{{ __('pharma::messages.pending') }}</option>
            <option value="delivered">{{ __('pharma::messages.delivered') }}</option>
            <option value="cancelled">{{ __('pharma::messages.cancelled') }}</option>
        </select>
        <div class="invalid-feedback">{{ __('pharma::messages.order_status_required') }}</div>
    </div>
    <div class="mb-3">
        <label for="payment_status" class="form-label">{{ __('pharma::messages.payment_status') }}</label><span
            class="text-danger">*</span>
        <select name="payment_status" id="payment_status" class="form-control select2">
            <option value="unpaid">{{ __('pharma::messages.unpaid') }}</option>
            <option value="paid">{{ __('pharma::messages.paid') }}</option>
        </select>
        <div class="invalid-feedback">{{ __('pharma::messages.order_status_required') }}</div>
    </div>

    <div class="mb-3">
        <label for="delivery_date" class="form-label">
            {{ __('pharma::messages.delivery_date') }}
            <span class="text-danger">*</span>
        </label>
        <div class="input-group mb-1">
            <input type="text" name="delivery_date" id="delivery_date" class="form-control delivery-date-picker"
                placeholder="{{ __('pharma::messages.placeholder_delivery_date') }}" required>
            <span class="input-group-text"><i class="ph ph-calendar"></i></span>
        </div>
        <div class="invalid-feedback" id="delivery_date_error">
            {{ __('pharma::messages.delivery_date_required') }}
        </div>
    </div>


    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">{{ __('pharma::messages.place_order') }}</button>
    </div>
</form>

<script>
    $(document).off('submit', '#orderMedicineForm').on('submit', '#orderMedicineForm', function(e) {
        e.preventDefault();

        const form = this;
        let isValid = true;

        // Clear previous validation states
        $(form).find('input').removeClass('is-invalid');
        $('#delivery_date_error').hide();

        // Validate quantity
        const quantity = form.quantity.value.trim();
        if (!quantity || parseInt(quantity) < 1) {
            $('#quantity').addClass('is-invalid');
            isValid = false;
        }

        // Validate delivery_date
        const deliveryDate = form.delivery_date.value.trim();
        if (!deliveryDate) {
            $('#delivery_date').addClass('is-invalid');
            $('#delivery_date_error').show(); // show error explicitly
            isValid = false;
        }

        // Stop submission if validation fails
        if (!isValid) {
            return;
        }

        // If validation passes, proceed with AJAX submit
        const submitBtn = $(form).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('{{ __('pharma::messages.placing...') }}');

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: $(form).serialize(),
            success: function(response) {
                submitBtn.prop('disabled', false).text('{{ __('pharma::messages.place_order') }}');
                $('#orderMedicineOffcanvas').offcanvas('hide');
                window.successSnackbar("{{ __('pharma::messages.order_saved_successfully') }}");
                form.reset();
                window.renderedDataTable.ajax.reload(null, false);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text('{{ __('pharma::messages.place_order') }}');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.quantity) {
                        $('#quantity').addClass('is-invalid');
                    }
                    if (errors.delivery_date) {
                        $('#delivery_date').addClass('is-invalid');
                        $('#delivery_date_error').show();
                    }
                    return; // Don't show snackbar on validation error
                }

                let message = '{{ __('pharma::messages.something_went_wrong') }}';
                window.successSnackbar(message);
            }
        });
    });

    function calculateTotalAmount() {
        const quantity = parseInt($('#quantity').val()) || 0;
        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
        const total = quantity * purchasePrice;
        $('#total_amount').val(total.toFixed(2));
    }

    $(document).on('input', '#quantity', function() {
        calculateTotalAmount();
    });

    // On document ready or modal open
    $(document).ready(function() {
        calculateTotalAmount();
    });

    // Disallow decimal input for quantity field
    $(document).on('input', '#quantity', function() {
        let val = $(this).val();
        val = val.replace(/\D/g, ''); // Remove non-digits
        $(this).val(val);
    });

    // Block typing "." and "," characters
    $(document).on('keydown', '#quantity', function(e) {
        if (e.key === '.' || e.key === ',') {
            e.preventDefault();
        }
    });

    // Block pasting decimals
    $(document).on('paste', '#quantity', function(e) {
        const pasted = (e.originalEvent || e).clipboardData.getData('text');
        if (!/^\d+$/.test(pasted)) {
            e.preventDefault();
        }
    });
</script>
