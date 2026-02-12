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
         
                   
                    
                    <!-- Simple table without DataTables for now -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="labs-simple-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lab Code</th>
                                    <th>Name</th>
                                    <th>Clinic</th>
                                    <th>Lab User</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($allLabs))
                                    @foreach($allLabs as $lab)
                                        <tr>
                                            <td>{{ $lab->id }}</td>
                                            <td>{{ $lab->lab_code }}</td>
                                            <td>{{ $lab->name }}</td>
                                            <td>{{ $lab->clinic ? $lab->clinic->name : 'N/A' }}</td>
                                            <td>{{ $lab->user ? $lab->user->first_name . ' ' . $lab->user->last_name : 'N/A' }}</td>
                                            <td>{{ $lab->phone_number ?? 'N/A' }}</td>
                                            <td>{{ $lab->email }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" 
                                                           data-id="{{ $lab->id }}" {{ $lab->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('backend.labs.edit', $lab->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                            data-url="{{ route('backend.labs.destroy', $lab->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No labs found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- DataTables table (hidden for now) -->
                    <div class="table-responsive" style="display: none;">
                        <table id="labs-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Lab Code</th>
                                    <th>Name</th>
                                    <th>Clinic</th>
                                    <th>Lab User</th>
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
    console.log('Document ready, labs page loaded');
    
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
                    location.reload();
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
