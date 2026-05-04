{{-- Patient Referral Modal — Doctor creates referral from encounter --}}
<div class="modal fade" id="addReferral" tabindex="-1" aria-labelledby="referralModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0" id="referralModalLabel">
                    <i class="ph ph-users me-1"></i> New Referral
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

                <form id="referral-form">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $data->user_id ?? '' }}">
                    <input type="hidden" name="referred_by" value="{{ $data->doctor_id ?? '' }}">
                    <input type="hidden" name="status" value="pending">
                    <input type="hidden" name="referral_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">

                    {{-- Referred To Doctor --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Refer To Doctor <span class="text-danger">*</span></label>
                        <select class="form-select" id="referral_to" name="referred_to" required>
                            <option value="">— Select a doctor to refer to —</option>
                            @foreach ($data['doctors_list'] ?? [] as $doctor)
                                @if ($doctor['id'] != $data->doctor_id)
                                    <option value="{{ $doctor['id'] }}">{{ $doctor['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Referral <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="referral_reason" name="reason" rows="3"
                            placeholder="Explain why you are referring this patient..." required></textarea>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control" id="referral_notes" name="notes" rows="2"
                            placeholder="Any additional information..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="referral_submit_btn" onclick="saveReferral()">
                    <span id="referral_submit_spinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                    <i class="ph ph-paper-plane-tilt me-1"></i> Create Referral
                </button>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
(function () {
    // Reset form when modal opens
    $('#addReferral').on('show.bs.modal', function () {
        $('#referral-form')[0].reset();
        $('#referral_submit_btn').prop('disabled', false);
    });

    window.saveReferral = function () {
        const btn = $('#referral_submit_btn');
        btn.prop('disabled', true);
        $('#referral_submit_spinner').show();

        const formData = $('#referral-form').serialize();

        $.ajax({
            url: '/app/patientreferral',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        })
        .done(function (res) {
            $('#addReferral').modal('hide');
            if (typeof window.successSnackbar === 'function') {
                window.successSnackbar('Referral created successfully.');
            }
            // Reload page to show the new referral in the table
            setTimeout(function () { window.location.reload(); }, 800);
        })
        .fail(function (xhr) {
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.errors
                ? Object.values(xhr.responseJSON.errors || {}).flat().join(' ')
                : 'Failed to create referral.';
            window.errorSnackbar ? window.errorSnackbar(msg) : alert(msg);
        })
        .always(function () {
            btn.prop('disabled', false);
            $('#referral_submit_spinner').hide();
        });
    };

    // Reset on close
    $('#addReferral').on('hidden.bs.modal', function () {
        $('#referral-form')[0].reset();
    });
})();
</script>
@endpush
