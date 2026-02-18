{{-- Lab Orders table shown inside the Patient Encounter page --}}
<div class="table-responsive rounded mb-0">
    <table class="table table-sm align-middle m-0" id="lab_order_table">
        <thead class="table-light">
            <tr>
                <th>Order #</th>
                <th>Lab</th>
                <th>Services</th>
                <th>Status</th>
                <th>Ordered</th>
                <th>Result</th>
                @if (($data['status'] ?? 0) == 1)
                    <th class="text-end">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data['lab_orders'] ?? [] as $labOrder)
                @php
                    $statusColor = match($labOrder['status'] ?? 'pending') {
                        'completed'  => 'success',
                        'in_progress'=> 'warning',
                        'cancelled'  => 'danger',
                        default      => 'secondary',
                    };
                    $services    = $labOrder['services'] ?? [];
                    $resultFile  = $labOrder['result_file'] ?? null;
                    $techNote    = $labOrder['technician_note'] ?? null;
                    $uploadedAt  = $labOrder['result_uploaded_at'] ?? null;
                @endphp
                <tr>
                    <td><span class="fw-semibold text-primary">#{{ $labOrder['order_number'] }}</span></td>
                    <td>{{ $labOrder['lab_name'] ?? '—' }}</td>
                    <td>
                        @forelse ($services as $svc)
                            <span class="badge bg-light text-dark border me-1">{{ $svc['service_name'] }}</span>
                        @empty
                            <span class="text-muted small">—</span>
                        @endforelse
                    </td>
                    <td>
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($labOrder['status'] ?? 'pending') }}</span>
                    </td>
                    <td class="text-nowrap small text-muted">
                        {{ isset($labOrder['order_date']) ? \Carbon\Carbon::parse($labOrder['order_date'])->format('d M Y') : '—' }}
                    </td>
                    <td>
                        @if ($labOrder['status'] === 'completed' && $resultFile)
                            @foreach (explode(',', $resultFile) as $file)
                                <a href="{{ asset('storage/' . trim($file)) }}" target="_blank"
                                   class="btn btn-sm btn-outline-success py-0 px-1 me-1" title="View result file">
                                    <i class="ph ph-file-arrow-down"></i>
                                </a>
                            @endforeach
                            @if ($techNote)
                                <span class="text-muted small d-block mt-1" title="{{ $techNote }}">
                                    <i class="ph ph-note"></i> {{ \Illuminate\Support\Str::limit($techNote, 60) }}
                                </span>
                            @endif
                            @if ($uploadedAt)
                                <span class="text-muted" style="font-size:0.7rem;">
                                    {{ \Carbon\Carbon::parse($uploadedAt)->format('d M Y H:i') }}
                                </span>
                            @endif
                        @elseif ($labOrder['status'] === 'completed')
                            <span class="text-muted small">Result recorded</span>
                        @else
                            <span class="text-muted small">Pending</span>
                        @endif
                    </td>
                    @if (($data['status'] ?? 0) == 1)
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                onclick="destroyLabOrder({{ $labOrder['id'] }})"
                                title="Delete order">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        <i class="ph ph-flask me-1"></i> No lab orders yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('after-scripts')
<script>
function destroyLabOrder(id) {
    if (typeof confirmDeleteSwal === 'function') {
        confirmDeleteSwal({ message: 'Delete this lab order?' }).then(result => {
            if (result.isConfirmed) _doDeleteLabOrder(id);
        });
    } else if (confirm('Delete this lab order?')) {
        _doDeleteLabOrder(id);
    }
}

function _doDeleteLabOrder(id) {
    $.ajax({
        url: '/app/lab-orders/delete/' + id,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (res.html) {
                $('#lab_order_table').html(res.html);
            }
            window.successSnackbar
                ? window.successSnackbar(res.message || 'Lab order deleted.')
                : alert(res.message || 'Deleted.');
        },
        error: function () {
            window.errorSnackbar
                ? window.errorSnackbar('Failed to delete lab order.')
                : alert('Failed to delete.');
        }
    });
}
</script>
@endpush
