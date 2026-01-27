@extends('backend.layouts.app')

@section('title', 'Lab Results')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Results Management</h4>
                    @can('create_lab_results')
                    <a href="{{ route('backend.lab-results.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Result
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <select id="status-filter" class="form-select" style="width: 200px;">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="lab-results-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Result Code</th>
                                    <th>Test Name</th>
                                    <th>Patient</th>
                                    <th>Test Date</th>
                                    <th>Status</th>
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
    var table = $('#lab-results-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("backend.lab-results.index_data") }}',
            data: function(d) {
                d.status = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'result_code', name: 'result_code' },
            { data: 'test_name', name: 'labTest.test_name' },
            { data: 'patient_name', name: 'patient.name' },
            { data: 'test_date', name: 'test_date' },
            { data: 'status_badge', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#status-filter').on('change', function() {
        table.ajax.reload();
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        var url = $(this).data('url');
        
        if (confirm('Are you sure you want to delete this result?')) {
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
                error: function() {
                    toastr.error('Failed to delete result');
                }
            });
        }
    });
});
</script>
@endpush
