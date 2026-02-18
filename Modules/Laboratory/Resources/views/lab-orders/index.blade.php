@extends('backend.layouts.app')

@section('title', 'Lab Orders')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Lab Orders</h4>
            <p class="text-muted small mb-0">All lab orders across all statuses</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('backend.lab-orders.worklist') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list-check me-1"></i> Worklist
            </a>
            <a href="{{ route('backend.lab-results.index') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-flask me-1"></i> Results
            </a>
            @can('create_lab_orders')
            <a href="{{ route('backend.lab-orders.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Order
            </a>
            @endcan
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
                <select name="status" class="form-select form-select-sm" style="width:160px;">
                    <option value="">All Statuses</option>
                    @foreach(['pending','confirmed','in_progress','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ',$s)) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('backend.lab-orders.index') }}" class="btn btn-sm btn-light">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Clinic</th>
                            <th>Lab</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Order Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $statusMap = [
                                    'completed'   => 'success',
                                    'in_progress' => 'warning',
                                    'confirmed'   => 'info',
                                    'cancelled'   => 'danger',
                                    'pending'     => 'secondary',
                                ];
                                $cls = $statusMap[$order->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $order->order_number }}</strong>
                                    @if($order->priority === 'urgent' || $order->priority === 'stat')
                                        <span class="badge bg-danger ms-1">{{ strtoupper($order->priority) }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($order->patient)->full_name ?? '—' }}</td>
                                <td>Dr. {{ optional($order->doctor)->full_name ?? '—' }}</td>
                                <td>{{ optional($order->clinic)->name ?? '—' }}</td>
                                <td>{{ optional($order->lab)->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $cls }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
                                <td>{{ number_format($order->final_amount, 2) }}</td>
                                <td>{{ $order->order_date ? $order->order_date->format('d M Y') : '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('backend.lab-orders.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status !== 'completed')
                                    <a href="{{ route('backend.lab-orders.edit', $order->id) }}"
                                       class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    <button class="btn btn-sm btn-outline-danger delete-order-btn"
                                        data-id="{{ $order->id }}"
                                        data-url="{{ route('backend.lab-orders.destroy', $order->id) }}"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No lab orders found.
                                    <a href="{{ route('backend.lab-orders.create') }}">Create one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Delete confirmation modal --}}
<form id="delete-order-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('after-scripts')
<script>
$(document).on('click', '.delete-order-btn', function () {
    if (!confirm('Delete this lab order? This cannot be undone.')) return;
    var url  = $(this).data('url');
    var row  = $(this).closest('tr');
    $.ajax({
        url: url,
        method: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
            row.fadeOut(300, function () { $(this).remove(); });
            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Order deleted.');
        },
        error: function (xhr) {
            if (typeof toastr !== 'undefined') toastr.error(xhr.responseJSON?.message || 'Failed to delete.');
            else alert('Failed to delete order.');
        }
    });
});
</script>
@endpush
