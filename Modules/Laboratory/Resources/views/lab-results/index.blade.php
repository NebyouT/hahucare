@extends('backend.layouts.app')

@section('title', 'Lab Results')

@section('content')
<div class="container-fluid">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Lab Results</h4>
            <p class="text-muted small mb-0">Results uploaded from completed lab orders</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('backend.lab-orders.worklist') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list-check me-1"></i> Worklist
            </a>
            <a href="{{ route('backend.lab-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-flask me-1"></i> All Orders
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
                    placeholder="Order # or patient name…" value="{{ request('search') }}">
                <select name="lab_id" class="form-select form-select-sm" style="width:180px;">
                    <option value="">All Labs</option>
                    @foreach($labs as $lab)
                        <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" style="width:150px;"
                    value="{{ request('date_from') }}" placeholder="From">
                <input type="date" name="date_to" class="form-control form-control-sm" style="width:150px;"
                    value="{{ request('date_to') }}" placeholder="To">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search','lab_id','date_from','date_to']))
                    <a href="{{ route('backend.lab-results.index') }}" class="btn btn-sm btn-light">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Results list --}}
    @forelse($orders as $order)
        @php
            $firstItem = $order->labOrderItems->first();
            $statusColor = match($order->status) {
                'completed'   => 'success',
                'in_progress' => 'warning',
                'cancelled'   => 'danger',
                default       => 'secondary',
            };
        @endphp

        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body p-0">
                <div class="row g-0 align-items-stretch">

                    {{-- Status stripe --}}
                    <div class="col-auto d-flex align-items-stretch">
                        <div class="rounded-start px-2 bg-{{ $statusColor }}" style="width:6px;"></div>
                    </div>

                    {{-- Main content --}}
                    <div class="col p-3">

                        {{-- Top row: order number + badges + date --}}
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                            <span class="fw-bold text-primary fs-6">#{{ $order->order_number }}</span>
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
                            @if($order->priority === 'urgent' || $order->priority === 'stat')
                                <span class="badge bg-danger">{{ strtoupper($order->priority) }}</span>
                            @endif
                            <span class="text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $order->order_date ? $order->order_date->format('d M Y') : '—' }}
                            </span>
                            @if($order->completed_date)
                                <span class="text-success small">
                                    <i class="fas fa-check-circle me-1"></i>Completed {{ $order->completed_date->format('d M Y H:i') }}
                                </span>
                            @endif
                        </div>

                        {{-- Patient / Doctor / Lab row --}}
                        <div class="row g-2 mb-2">
                            <div class="col-sm-3">
                                <span class="text-muted small d-block">Patient</span>
                                <strong>{{ optional($order->patient)->full_name ?? '—' }}</strong>
                            </div>
                            <div class="col-sm-3">
                                <span class="text-muted small d-block">Doctor</span>
                                <span>Dr. {{ optional($order->doctor)->full_name ?? '—' }}</span>
                            </div>
                            <div class="col-sm-3">
                                <span class="text-muted small d-block">Lab</span>
                                <span>{{ optional($order->lab)->name ?? '—' }}</span>
                            </div>
                            <div class="col-sm-3">
                                <span class="text-muted small d-block">Clinic</span>
                                <span>{{ optional($order->clinic)->name ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Services ordered --}}
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach($order->labOrderItems as $item)
                                <span class="badge bg-light text-dark border">{{ $item->service_name ?? '—' }}</span>
                            @endforeach
                        </div>

                        {{-- Doctor's clinical note --}}
                        @if($order->notes)
                            <div class="text-muted small mb-2">
                                <i class="fas fa-notes-medical me-1"></i><em>{{ $order->notes }}</em>
                            </div>
                        @endif

                        {{-- Result section --}}
                        @if($firstItem && $firstItem->result_file)
                            <div class="border rounded p-2 bg-light mt-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="text-success fw-semibold small">
                                        <i class="fas fa-check-circle me-1"></i>Result Available
                                    </span>
                                    @foreach(explode(',', $firstItem->result_file) as $idx => $file)
                                        <a href="{{ asset('storage/' . trim($file)) }}" target="_blank"
                                           class="btn btn-sm btn-outline-success py-0">
                                            <i class="fas fa-file-download me-1"></i>File {{ $idx + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                                @if($firstItem->technician_note)
                                    <div class="text-muted small">
                                        <i class="fas fa-user-md me-1"></i><strong>Technician note:</strong> {{ $firstItem->technician_note }}
                                    </div>
                                @endif
                                @if($firstItem->result_uploaded_at)
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-clock me-1"></i>Uploaded {{ $firstItem->result_uploaded_at->format('d M Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-muted small mt-1">
                                <i class="fas fa-hourglass-half me-1"></i>No result uploaded yet
                            </div>
                        @endif

                    </div>

                    {{-- Right: action buttons --}}
                    <div class="col-auto d-flex flex-column align-items-center justify-content-center p-3 border-start gap-2">
                        <a href="{{ route('backend.lab-orders.show', $order->id) }}"
                           class="btn btn-sm btn-outline-primary" title="View order detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($order->status !== 'completed')
                            <button type="button" class="btn btn-sm btn-primary upload-result-btn"
                                data-order-id="{{ $order->id }}"
                                data-order-number="{{ $order->order_number }}"
                                title="Upload result">
                                <i class="fas fa-upload"></i>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-flask" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">No lab results found.</p>
                <a href="{{ route('backend.lab-orders.worklist') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-list-check me-1"></i> Go to Worklist
                </a>
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-3">
        {{ $orders->links() }}
    </div>

</div>

{{-- Reuse the same Add Result modal from worklist --}}
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0"><i class="fas fa-upload me-1"></i> Upload Result</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="result-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2">
                        <span class="text-muted small">Order</span>
                        <strong id="rm_order_number" class="ms-2 text-primary"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Result File(s) <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small">— PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, TXT, RTF (max 10 MB each)</span>
                        </label>
                        <input type="file" id="result_files" name="result_files[]"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.txt,.rtf" multiple class="form-control">
                        <div id="file_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Technician Note <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control" name="technician_note" rows="3"
                            placeholder="Observations, summary, or remarks…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="rm_submit_btn">
                        <span id="rm_spinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                        <i class="fas fa-check me-1"></i> Save & Mark Completed
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
            div.innerHTML = '<i class="fas fa-file text-primary"></i><span>' + file.name + '</span><span class="text-muted">(' + Math.round(file.size/1024) + ' KB)</span>';
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
