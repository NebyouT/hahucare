@extends('backend.layouts.app')

@section('title', 'Lab Worklist')

@section('content')
<div class="container-fluid">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Lab Worklist</h4>
            <p class="text-muted small mb-0">Pending orders waiting for results</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="{{ route('backend.lab-orders.worklist') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Order # or patient name…" value="{{ request('search') }}" style="width:220px;">
                <select name="status" class="form-select form-select-sm" style="width:150px;">
                    <option value="">Pending + In Progress</option>
                    <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                    <option value=""            {{ request('status') === ''            ? 'selected' : '' }}>All</option>
                </select>
                <button class="btn btn-sm btn-primary">Filter</button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('backend.lab-orders.worklist') }}" class="btn btn-sm btn-light">Clear</a>
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ph ph-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ph ph-warning me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Orders list --}}
    @forelse ($orders as $order)
        @php
            $statusColor = match($order->status) {
                'completed'  => 'success',
                'in_progress'=> 'warning',
                'cancelled'  => 'danger',
                default      => 'secondary',
            };
            $isCompleted = $order->status === 'completed';
        @endphp

        <div class="card mb-3 shadow-sm border-0 {{ $isCompleted ? 'opacity-75' : '' }}">
            <div class="card-body p-0">
                <div class="row g-0 align-items-stretch">

                    {{-- Left: status stripe --}}
                    <div class="col-auto d-flex align-items-stretch">
                        <div class="rounded-start px-2 bg-{{ $statusColor }}" style="width:6px;"></div>
                    </div>

                    {{-- Centre: order info --}}
                    <div class="col p-3">
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                            <span class="fw-bold text-primary fs-6">#{{ $order->order_number }}</span>
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                            <span class="text-muted small">
                                <i class="ph ph-calendar me-1"></i>
                                {{ $order->order_date ? $order->order_date->format('d M Y H:i') : '—' }}
                            </span>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Patient</span>
                                <strong>{{ optional($order->patient)->full_name ?? '—' }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Doctor</span>
                                <span>Dr. {{ optional($order->doctor)->full_name ?? '—' }}</span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Lab</span>
                                <span>{{ optional($order->lab)->name ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Services --}}
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($order->labOrderItems as $item)
                                <span class="badge bg-light text-dark border">{{ $item->service_name }}</span>
                            @endforeach
                        </div>

                        {{-- Clinical note from doctor --}}
                        @if ($order->notes)
                            <div class="mt-2 text-muted small">
                                <i class="ph ph-note me-1"></i><em>{{ $order->notes }}</em>
                            </div>
                        @endif

                        {{-- Result (if completed) --}}
                        @if ($isCompleted)
                            @php $firstItem = $order->labOrderItems->first(); @endphp
                            @if ($firstItem && $firstItem->result_file)
                                <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                    <span class="text-success small fw-semibold"><i class="ph ph-check-circle me-1"></i>Result uploaded</span>
                                    @foreach (explode(',', $firstItem->result_file) as $idx => $file)
                                        <a href="{{ asset('storage/' . trim($file)) }}" target="_blank"
                                           class="btn btn-sm btn-outline-success py-0">
                                            <i class="ph ph-file-arrow-down me-1"></i>File {{ $idx + 1 }}
                                        </a>
                                    @endforeach
                                    @if ($firstItem->technician_note)
                                        <span class="text-muted small">— {{ $firstItem->technician_note }}</span>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Right: action --}}
                    @if (!$isCompleted)
                        <div class="col-auto d-flex align-items-center p-3 border-start">
                            <button type="button" class="btn btn-primary btn-sm upload-result-btn"
                                data-order-id="{{ $order->id }}"
                                data-order-number="{{ $order->order_number }}"
                                title="Upload result for this order">
                                <i class="ph ph-upload-simple me-1"></i> Add Result
                            </button>
                        </div>
                    @else
                        <div class="col-auto d-flex align-items-center p-3 border-start">
                            <span class="text-success small"><i class="ph ph-check-circle fs-5"></i></span>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="ph ph-flask" style="font-size:3rem;"></i>
                <p class="mt-2 mb-0">No orders found.</p>
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-3">
        {{ $orders->links() }}
    </div>

</div>

{{-- ============================================================
     Add Result Modal
     ============================================================ --}}
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0" id="resultModalLabel">
                    <i class="ph ph-upload-simple me-1"></i> Upload Result
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="result-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">

                    {{-- Order context --}}
                    <div class="alert alert-light border mb-3 py-2">
                        <span class="text-muted small">Order</span>
                        <strong id="rm_order_number" class="ms-2 text-primary"></strong>
                    </div>

                    {{-- File upload --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Result File(s) <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small">— PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, TXT, RTF (max 10 MB each)</span>
                        </label>
                        <input type="file" id="result_files" name="result_files[]"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt,.rtf"
                               multiple class="form-control">
                        <div id="file_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>

                    {{-- Technician note --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            Technician Note <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea class="form-control" name="technician_note" rows="3"
                            placeholder="Observations, summary, or remarks about the findings…"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="rm_submit_btn">
                        <span id="rm_spinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                        <i class="ph ph-check me-1"></i> Save & Mark Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
function openResultModal(orderId, orderNumber) {
    document.getElementById('rm_order_number').textContent = '#' + orderNumber;
    document.getElementById('result-form').action = '/app/lab-orders/store-result/' + orderId;
    document.getElementById('result-form').reset();
    document.getElementById('file_preview').innerHTML = '';
    new bootstrap.Modal(document.getElementById('resultModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle upload button clicks
    document.querySelectorAll('.upload-result-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const orderId = this.getAttribute('data-order-id');
            const orderNumber = this.getAttribute('data-order-number');
            openResultModal(orderId, orderNumber);
        });
    });

    // Handle file input change - show preview
    document.getElementById('result_files').addEventListener('change', function(e) {
        const preview = document.getElementById('file_preview');
        preview.innerHTML = '';
        Array.from(this.files).forEach(function(file) {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center gap-1 border rounded px-2 py-1 small';
            div.innerHTML = '<i class="ph ph-file text-primary"></i><span>' + file.name + '</span><span class="text-muted">(' + Math.round(file.size/1024) + ' KB)</span>';
            preview.appendChild(div);
        });
    });

    // Handle form submission
    document.getElementById('result-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('result_files');
        if (!fileInput.files.length) {
            alert('Please attach at least one result file.');
            return;
        }

        const btn = document.getElementById('rm_submit_btn');
        btn.disabled = true;
        document.getElementById('rm_spinner').style.display = 'inline-block';

        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('resultModal')).hide();
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Upload failed. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            document.getElementById('rm_spinner').style.display = 'none';
        });
    });
});
</script>
@endpush
