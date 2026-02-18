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
                            <button type="button" class="btn btn-sm btn-primary"
                                onclick="openResultModal({{ $order->id }}, '{{ addslashes($order->order_number) }}')"
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
                            <span class="text-muted fw-normal small">— PDF, JPG, PNG, DOCX (max 10 MB each)</span>
                        </label>
                        <div id="drop_zone" class="border border-2 rounded p-4 text-center text-muted"
                             style="cursor:pointer; border-style:dashed !important;">
                            <i class="fas fa-cloud-upload-alt" style="font-size:2rem;"></i>
                            <p class="mb-1 mt-1">Drag & drop files here, or <span class="text-primary">browse</span></p>
                            <input type="file" id="result_files" name="result_files[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc" multiple class="d-none">
                        </div>
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
    $('#rm_order_number').text('#' + orderNumber);
    $('#result-form').attr('action', '/app/lab-orders/store-result/' + orderId);
    $('#result-form')[0].reset();
    $('#file_preview').empty();
    $('#resultModal').modal('show');
}

$(document).ready(function () {
    $('#drop_zone').on('click', function () { $('#result_files').click(); });

    $('#drop_zone').on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-primary-subtle');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary-subtle');
        if (e.type === 'drop') handleFiles(e.originalEvent.dataTransfer.files);
    });

    $('#result_files').on('change', function () { handleFiles(this.files); });

    function handleFiles(files) {
        const preview = $('#file_preview');
        preview.empty();
        const dt = new DataTransfer();
        Array.from(files).forEach(function (f) {
            dt.items.add(f);
            const icon = f.type.includes('pdf') ? 'fa-file-pdf' : f.type.includes('image') ? 'fa-image' : 'fa-file-alt';
            preview.append(`<div class="d-flex align-items-center gap-1 border rounded px-2 py-1 small">
                <i class="fas ${icon} text-primary"></i>
                <span>${f.name}</span>
                <span class="text-muted">(${(f.size/1024).toFixed(0)} KB)</span>
            </div>`);
        });
        $('#result_files')[0].files = dt.files;
    }

    $('#result-form').on('submit', function (e) {
        e.preventDefault();
        if (!$('#result_files')[0].files.length) {
            alert('Please attach at least one result file.');
            return;
        }
        const btn = $('#rm_submit_btn');
        btn.prop('disabled', true);
        $('#rm_spinner').show();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                $('#resultModal').modal('hide');
                window.location.reload();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('\n')
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
