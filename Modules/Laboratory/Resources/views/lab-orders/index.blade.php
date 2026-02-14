@extends('backend.layouts.app')

@section('title', 'Lab Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Orders Management</h4>
                    @can('create_lab_orders')
                    <a href="{{ route('backend.lab-orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Order
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="status-filter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="lab-orders-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Clinic</th>
                                    <th>Lab</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#lab-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("backend.lab-orders.index_data") }}',
            data: function(d) {
                d.status = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'order_number', name: 'order_number' },
            { data: 'patient_name', name: 'patient.first_name' },
            { data: 'doctor_name', name: 'doctor.first_name' },
            { data: 'clinic_name', name: 'clinic.name' },
            { data: 'lab_name', name: 'lab.name' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'amount', name: 'final_amount' },
            { data: 'order_date', name: 'order_date', render: function(data) {
                return data ? moment(data).format('YYYY-MM-DD HH:mm') : '-';
            }},
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Status filter
    $('#status-filter').on('change', function() {
        table.ajax.reload();
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        var url = $(this).data('url');
        
        if (confirm('Are you sure you want to delete this order?')) {
            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message || 'Failed to delete order');
                }
            });
        }
    });
});
</script>
@endpush
