<form class="requires-validation" id="orderMedicineForm"
    action="{{ route('backend.order-medicine.update', $orderDetail->id) }}" novalidate>
    @method('PUT')
    @csrf
    <input type="hidden" name="medicine_id" value="{{ $orderDetail->medicine_id }}">

    <div class="mb-3">
        <label for="quantity" class="form-label">{{ __('pharma::messages.quantity') }}<span
                class="text-danger">*</span></label>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1"
            placeholder="{{ __('pharma::messages.placeholder_quantity') }}" value="{{ $orderDetail->quantity }}"
            required>
        <div class="invalid-feedback">{{ __('pharma::messages.quantity_required') }}</div>
    </div>

    <div class="mb-3">
        <label for="delivery_date" class="form-label">{{ __('pharma::messages.delivery_date') }}<span
                class="text-danger">*</span></label>
        <input type="text" name="delivery_date" id="delivery_date" class="form-control"
            placeholder="{{ __('pharma::messages.placeholder_delivery_date') }}"
            value="{{ $orderDetail->delivery_date }}" required>
        <div class="invalid-feedback">{{ __('pharma::messages.delivery_date_required') }}</div>
    </div>

    <div class="mb-3">
        <label for="order_status" class="form-label">{{ __('pharma::messages.order_status') }}</label>
        <select name="order_status" id="order_status" class="form-control select2">
            <option value="pending" class="text-capitalize"
                {{ $orderDetail->order_status == 'pending' ? 'selected' : '' }}>
                {{ ucfirst(config('constant.ORDER_STATUS.PENDING')) }}
            </option>
            <option value="delivered" class="text-capitalize"
                {{ $orderDetail->order_status == 'delivered' ? 'selected' : '' }}>
                {{ ucfirst(config('constant.ORDER_STATUS.DELIVERED')) }}
            </option>
            <option value="cancelled" class="text-capitalize"
                {{ $orderDetail->order_status == 'cancelled' ? 'selected' : '' }}>
                {{ ucfirst(config('constant.ORDER_STATUS.CANCELLED')) }}
            </option>
        </select>
    </div>
    <div class="mb-3">
        <label for="payment_status" class="form-label">{{ __('pharma::messages.payment_status') }}</label>
        <select name="payment_status" id="payment_status" class="form-control select2">
            <option value="unpaid" {{ $orderDetail->payment_status == 'unpaid' ? 'selected' : '' }}>
                {{ ucfirst(__('pharma::messages.unpaid')) }}
            </option>
            <option value="paid" {{ $orderDetail->payment_status == 'paid' ? 'selected' : '' }}>
                {{ ucfirst(__('pharma::messages.paid')) }}
            </option>
        </select>
    </div>


    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">{{ __('pharma::messages.place_order') }}</button>
    </div>
</form>



<script>
    function initFlatpickr() {
        if ($('#delivery_date').length) {
            flatpickr("#delivery_date", {
                dateFormat: "Y-m-d",
                allowInput: true,
                defaultDate: new Date(),
            });
        }
    }
    $(document).ready(function() {
        initFlatpickr();

        $('#OrderDetailOffcanvas').on('shown.bs.offcanvas', function() {
            initFlatpickr();
        });

        // Submit Handler
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

            if (!isValid) return;

            const submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('{{ __('pharma::messages.placing...') }}');

            $.ajax({
                url: $(form).attr('action'),
                method: 'POST',
                data: $(form).serialize(),
                success: function(response) {
                    submitBtn.prop('disabled', false).text(
                        '{{ __('pharma::messages.place_order') }}');
                    $('#OrderDetailOffcanvas').offcanvas('hide');
                    window.successSnackbar(
                        "{{ __('pharma::messages.order_saved_successfully') }}");
                    form.reset();
                    window.renderedDataTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(
                        '{{ __('pharma::messages.place_order') }}');
                    let message = '{{ __('pharma::messages.something_went_wrong') }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    window.successSnackbar(message);
                    form.reset();
                }
            });
        });

        // Quantity input validation
        $(document).on('input', '#quantity', function() {
            let val = $(this).val();
            val = val.replace(/\D/g, '');
            $(this).val(val);
        });

        $(document).on('keydown', '#quantity', function(e) {
            if (e.key === '.' || e.key === ',') {
                e.preventDefault();
            }
        });

        $(document).on('paste', '#quantity', function(e) {
            const pasted = (e.originalEvent || e).clipboardData.getData('text');
            if (!/^\d+$/.test(pasted)) {
                e.preventDefault();
            }
        });
    });
</script>
