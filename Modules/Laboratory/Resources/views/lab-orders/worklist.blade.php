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
                            <button type="button" class="btn btn-primary btn-sm"
                                onclick="openResultModal({{ $order->id }}, '{{ addslashes($order->order_number) }}')"
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
                            <span class="text-muted fw-normal small">— PDF, JPG, PNG, DOCX (max 10 MB each)</span>
                        </label>
                        <div id="drop_zone"
                             class="border border-2 border-dashed rounded p-4 text-center text-muted"
                             style="cursor:pointer; border-style:dashed !important;">
                            <i class="ph ph-cloud-arrow-up" style="font-size:2rem;"></i>
                            <p class="mb-1 mt-1">Drag & drop files here, or <span class="text-primary">browse</span></p>
                            <input type="file" id="result_files" name="result_files[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc"
                                   multiple class="d-none">
                        </div>
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
    $('#rm_order_number').text('#' + orderNumber);
    $('#result-form').attr('action', '/app/lab-orders/store-result/' + orderId);
    $('#result-form')[0].reset();
    $('#file_preview').empty();
    $('#resultModal').modal('show');
}

$(document).ready(function () {
    // Drop zone click → open file picker
    $('#drop_zone').on('click', function () { $('#result_files').click(); });

    // Drag-over styling
    $('#drop_zone').on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-primary-subtle');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary-subtle');
        if (e.type === 'drop') {
            handleFiles(e.originalEvent.dataTransfer.files);
        }
    });

    // File input change
    $('#result_files').on('change', function () {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        const preview = $('#file_preview');
        preview.empty();
        const dt = new DataTransfer();
        Array.from(files).forEach(function (f) {
            dt.items.add(f);
            const icon = f.type.includes('pdf') ? 'ph-file-pdf' :
                         f.type.includes('image') ? 'ph-image' : 'ph-file-doc';
            preview.append(`
                <div class="d-flex align-items-center gap-1 border rounded px-2 py-1 small">
                    <i class="ph ${icon} text-primary"></i>
                    <span>${f.name}</span>
                    <span class="text-muted">(${(f.size/1024).toFixed(0)} KB)</span>
                </div>`);
        });
        // Assign files back to input
        $('#result_files')[0].files = dt.files;
    }

    // Form submit via AJAX
    $('#result-form').on('submit', function (e) {
        e.preventDefault();

        if (!$('#result_files')[0].files.length) {
            alert('Please attach at least one result file.');
            return;
        }

        const btn = $('#rm_submit_btn');
        btn.prop('disabled', true);
        $('#rm_spinner').show();

        const formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#resultModal').modal('hide');
                // Reload page to reflect completed status
                window.location.reload();
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                const msg = errors
                    ? Object.values(errors).flat().join('\n')
                    : (xhr.responseJSON?.message || 'Upload failed.');
                alert(msg);
            },
            complete: function () {
                btn.prop('disabled', false);
                $('#rm_spinner').hide();
            }
        });
    });
});
</script>
@endpush
