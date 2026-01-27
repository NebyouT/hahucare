@extends('backend.layouts.app')

@section('title', 'Lab Tests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Tests Management</h4>
                    @can('create_lab_tests')
                    <a href="{{ route('backend.lab-tests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Test
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="lab-tests-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Test Code</th>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Duration (min)</th>
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
    var table = $('#lab-tests-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("backend.lab-tests.index_data") }}',
        columns: [
            { data: 'id', orderable: false, searchable: false, render: function(data) {
                return '<input type="checkbox" class="row-checkbox" value="' + data + '">';
            }},
            { data: 'test_code', name: 'test_code' },
            { data: 'test_name', name: 'test_name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'price', name: 'price', render: function(data) {
                return '$' + parseFloat(data).toFixed(2);
            }},
            { data: 'duration_minutes', name: 'duration_minutes' },
            { data: 'status', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Select all checkboxes
    $('#select-all').on('click', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });

    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        var id = $(this).data('id');
        var status = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{{ url("app/lab-tests/update-status") }}/' + id,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                toastr.success(response.message);
            },
            error: function() {
                toastr.error('Failed to update status');
            }
        });
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        var url = $(this).data('url');
        
        if (confirm('Are you sure you want to delete this test?')) {
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
                    toastr.error('Failed to delete test');
                }
            });
        }
    });
});
</script>
@endpush
