{{-- Patient Referrals table shown inside the Patient Encounter page --}}
<div class="table-responsive rounded mb-0">
    <table class="table table-sm align-middle m-0" id="referral_table">
        <thead class="table-light">
            <tr>
                <th>Referred By</th>
                <th>Referred To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Date</th>
                @if (($data['status'] ?? 0) == 1)
                    <th class="text-end">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data['patient_referrals'] ?? [] as $referral)
                @php
                    $statusColor = match($referral['status'] ?? 'pending') {
                        'accepted'  => 'success',
                        'rejected'  => 'danger',
                        'pending'   => 'warning',
                        default     => 'secondary',
                    };
                @endphp
                <tr>
                    <td>{{ $referral['referred_by'] ?? '—' }}</td>
                    <td>{{ $referral['referred_to'] ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($referral['reason'] ?? '', 50) }}</td>
                    <td>
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($referral['status'] ?? 'pending') }}</span>
                    </td>
                    <td class="text-nowrap small text-muted">
                        {{ isset($referral['referral_date']) ? \Carbon\Carbon::parse($referral['referral_date'])->format('d M Y') : '—' }}
                    </td>
                    @if (($data['status'] ?? 0) == 1)
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                onclick="destroyReferral({{ $referral['id'] }})"
                                title="Delete referral">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">
                        <i class="ph ph-users me-1"></i> No referrals yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('after-scripts')
<script>
function destroyReferral(id) {
    if (typeof confirmDeleteSwal === 'function') {
        confirmDeleteSwal({ message: 'Delete this referral?' }).then(result => {
            if (result.isConfirmed) _doDeleteReferral(id);
        });
    } else if (confirm('Delete this referral?')) {
        _doDeleteReferral(id);
    }
}

function _doDeleteReferral(id) {
    $.ajax({
        url: '/app/patientreferral/' + id,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (res.html) {
                $('#referral_table').html(res.html);
            }
            window.successSnackbar
                ? window.successSnackbar(res.message || 'Referral deleted.')
                : alert(res.message || 'Deleted.');
        },
        error: function () {
            window.errorSnackbar
                ? window.errorSnackbar('Failed to delete referral.')
                : alert('Failed to delete.');
        }
    });
}
</script>
@endpush
