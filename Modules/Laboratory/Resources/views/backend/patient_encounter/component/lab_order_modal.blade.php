{{-- Lab Order Modal — Doctor creates order from encounter --}}
<div class="modal fade" id="addLabOrder" tabindex="-1" aria-labelledby="labOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0" id="labOrderModalLabel">
                    <i class="ph ph-flask me-1"></i> New Lab Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Context strip --}}
                <div class="d-flex flex-wrap gap-3 mb-4 p-3 bg-light rounded">
                    <div><span class="text-muted small">Patient</span><br><strong>{{ optional($data->user)->full_name ?? '—' }}</strong></div>
                    <div class="vr"></div>
                    <div><span class="text-muted small">Doctor</span><br><strong>Dr. {{ optional($data->doctor)->full_name ?? '—' }}</strong></div>
                    <div class="vr"></div>
                    <div><span class="text-muted small">Clinic</span><br><strong>{{ optional($data->clinic)->name ?? '—' }}</strong></div>
                    <div class="vr"></div>
                    <div><span class="text-muted small">Encounter</span><br><strong>#{{ $data->id ?? '—' }}</strong></div>
                </div>

                <form id="lab-order-form">
                    @csrf
                    <input type="hidden" name="type"           value="encounter_lab_order">
                    <input type="hidden" name="clinic_id"      value="{{ $data->clinic_id ?? '' }}">
                    <input type="hidden" name="patient_id"     value="{{ $data->user_id ?? '' }}">
                    <input type="hidden" name="doctor_id"      value="{{ $data->doctor_id ?? '' }}">
                    <input type="hidden" name="encounter_id"   value="{{ $data->id ?? '' }}">
                    <input type="hidden" name="order_type"     value="outpatient">
                    <input type="hidden" name="priority"       value="routine">
                    <input type="hidden" name="collection_type" value="venipuncture">

                    {{-- Step 1: Lab --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">1. Select Lab <span class="text-danger">*</span></label>
                        <select class="form-select" id="lo_lab_id" name="lab_id" required>
                            <option value="">— Choose a lab —</option>
                        </select>
                    </div>

                    {{-- Step 2: Services (loads after lab chosen) --}}
                    <div class="mb-3" id="lo_services_wrap" style="display:none;">
                        <label class="form-label fw-semibold">2. Select Services <span class="text-danger">*</span></label>
                        <div id="lo_services_container">
                            <div class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary"></span> Loading…</div>
                        </div>
                    </div>

                    {{-- Step 3: Clinical Note --}}
                    <div class="mb-1" id="lo_note_wrap" style="display:none;">
                        <label class="form-label fw-semibold">3. Clinical Note <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control" id="lo_referral_notes" name="referral_notes" rows="3"
                            placeholder="Symptoms, special instructions, or clinical indication…"></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="lo_submit_btn" disabled onclick="saveLabOrder()">
                    <span id="lo_submit_spinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                    <i class="ph ph-paper-plane-tilt me-1"></i> Send to Lab
                </button>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
(function () {
    let loSelectedServices = [];

    // Load labs when modal opens
    $('#addLabOrder').on('show.bs.modal', function () {
        loSelectedServices = [];
        $('#lo_services_wrap').hide();
        $('#lo_note_wrap').hide();
        $('#lo_submit_btn').prop('disabled', true);
        $('#lo_lab_id').val('').trigger('change');

        const clinicId = {{ $data['clinic_id'] ?? 0 }};
        $.get(`/app/lab-orders/get-labs-by-clinic/${clinicId}`)
            .done(function (labs) {
                $('#lo_lab_id').empty().append('<option value="">— Choose a lab —</option>');
                if (labs.length) {
                    labs.forEach(function(l) {
                        const label = l.same_clinic
                            ? l.name
                            : l.name + (l.clinic_name ? ' (' + l.clinic_name + ')' : '');
                        $('#lo_lab_id').append(`<option value="${l.id}">${label}</option>`);
                    });
                } else {
                    $('#lo_lab_id').append('<option value="" disabled>No labs found</option>');
                }
            })
            .fail(function() {
                $('#lo_lab_id').append('<option value="" disabled>Error loading labs</option>');
            });
    });

    // Lab change → load services
    $('#lo_lab_id').on('change', function () {
        const labId = $(this).val();
        loSelectedServices = [];
        $('#lo_submit_btn').prop('disabled', true);

        if (!labId) {
            $('#lo_services_wrap').hide();
            $('#lo_note_wrap').hide();
            return;
        }

        $('#lo_services_wrap').show();
        $('#lo_note_wrap').show();
        $('#lo_services_container').html('<div class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary"></span> Loading services…</div>');

        $.get(`/app/lab-orders/get-services-by-lab/${labId}`)
            .done(function (services) {
                if (!services.length) {
                    $('#lo_services_container').html('<p class="text-muted small">No services found for this lab.</p>');
                    return;
                }
                let html = '<div class="row g-2">';
                services.forEach(function (s) {
                    html += `
                    <div class="col-sm-6">
                        <label class="d-flex align-items-start gap-2 p-2 border rounded cursor-pointer lo-service-card" for="lo_svc_${s.id}">
                            <input class="form-check-input mt-1 lo-service-cb flex-shrink-0" type="checkbox"
                                id="lo_svc_${s.id}" value="${s.id}">
                            <div>
                                <div class="fw-semibold">${s.name}</div>
                                ${s.description ? `<div class="text-muted small">${s.description}</div>` : ''}
                                ${s.price ? `<span class="badge bg-primary-subtle text-primary mt-1">${s.price}</span>` : ''}
                            </div>
                        </label>
                    </div>`;
                });
                html += '</div>';
                $('#lo_services_container').html(html);

                // Checkbox handler
                $(document).off('change', '.lo-service-cb').on('change', '.lo-service-cb', function () {
                    const id = $(this).val();
                    if ($(this).is(':checked')) {
                        loSelectedServices.push(id);
                        $(this).closest('.lo-service-card').addClass('border-primary bg-primary-subtle');
                    } else {
                        loSelectedServices = loSelectedServices.filter(x => x !== id);
                        $(this).closest('.lo-service-card').removeClass('border-primary bg-primary-subtle');
                    }
                    $('#lo_submit_btn').prop('disabled', loSelectedServices.length === 0);
                });
            })
            .fail(function () {
                $('#lo_services_container').html('<p class="text-danger small">Failed to load services.</p>');
            });
    });

    window.saveLabOrder = function () {
        if (!loSelectedServices.length) return;

        const btn = $('#lo_submit_btn');
        btn.prop('disabled', true);
        $('#lo_submit_spinner').show();

        const formData = $('#lab-order-form').serialize()
            + '&' + loSelectedServices.map(id => `services[]=${id}`).join('&');

        $.ajax({
            url: '/app/lab-orders',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
        .done(function (res) {
            $('#addLabOrder').modal('hide');
            if (typeof window.successSnackbar === 'function') {
                window.successSnackbar('Lab order created successfully.');
            }
            // Reload page to show the new order in the table
            setTimeout(function () { window.location.reload(); }, 800);
        })
        .fail(function (xhr) {
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.errors
                ? Object.values(xhr.responseJSON.errors || {}).flat().join(' ')
                : 'Failed to create lab order.';
            window.errorSnackbar ? window.errorSnackbar(msg) : alert(msg);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('#lo_submit_spinner').hide();
        });
    };

    // Reset on close
    $('#addLabOrder').on('hidden.bs.modal', function () {
        loSelectedServices = [];
        $('#lo_lab_id').val('');
        $('#lo_referral_notes').val('');
        $('#lo_services_wrap').hide();
        $('#lo_note_wrap').hide();
        $('#lo_submit_btn').prop('disabled', true);
    });
})();
</script>
@endpush
