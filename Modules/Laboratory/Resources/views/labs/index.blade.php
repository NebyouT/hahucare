@extends('backend.layouts.app')

@section('title', 'Labs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Labs Management</h4>
                    @can('create_labs')
                    <a href="{{ route('backend.labs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Lab
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="labs-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Lab Code</th>
                                    <th>Name</th>
                                    <th>Clinic</th>
                                    <th>Phone</th>
                                    <th>Email</th>
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
    var table = $('#labs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("backend.labs.index_data") }}',
        columns: [
            { data: 'id', orderable: false, searchable: false, render: function(data) {
                return '<input type="checkbox" class="row-checkbox" value="' + data + '">';
            }},
            { data: 'lab_code', name: 'lab_code' },
            { data: 'name', name: 'name' },
            { data: 'clinic_name', name: 'clinic.name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'email', name: 'email' },
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
            url: '{{ url("app/labs/update-status") }}/' + id,
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
        
        if (confirm('Are you sure you want to delete this lab?')) {
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
                    toastr.error(xhr.responseJSON.message || 'Failed to delete lab');
                }
            });
        }
    });
});
</script>
@endpush
