<form class="requires-validation" id="orderMedicineForm" action="{{ route('backend.order-medicine.store') }}" method="POST" novalidate>
    @csrf
    <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">

    <div class="mb-3">
        <label for="quantity" class="form-label">{{ __('pharma::messages.quantity') }}</label>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="{{ __('pharma::messages.placeholder_quantity') }}" required>
        <div class="invalid-feedback">{{ __('pharma::messages.quantity_required') }}</div>
    </div>

    <div class="mb-3">
        <label for="delivery_date" class="form-label">{{ __('pharma::messages.delivery_date') }}</label>
        <input type="date" name="delivery_date" id="delivery_date" class="form-control" placeholder="{{ __('pharma::messages.placeholder_delivery_date') }}" required>
        <div class="invalid-feedback">{{ __('pharma::messages.delivery_date_required') }}</div>
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
            isValid = false;
        }

        if (!isValid) {
            return; // Stop submission if validation fails
        }

        // If validation passes, proceed with AJAX submit
        const submitBtn = $(form).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('{{ __("pharma::messages.placing...") }}');

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: $(form).serialize(),
            success: function(response) {
                submitBtn.prop('disabled', false).text('{{ __("pharma::messages.place_order") }}');
                // Assuming you have an offcanvas with id orderMedicineOffcanvas
                $('#orderMedicineOffcanvas').offcanvas('hide');
                window.successSnackbar("{{ __('pharma::messages.order_saved_successfully') }}");
                form.reset();
                window.renderedDataTable.ajax.reload(null, false);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).text('{{ __("pharma::messages.place_order") }}');
                let message = '{{ __("pharma::messages.something_went_wrong") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                window.successSnackbar(message);
                form.reset();
            }
        });
    });
</script>
