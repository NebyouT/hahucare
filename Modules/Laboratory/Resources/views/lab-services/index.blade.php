@extends('backend.layouts.app')

@section('title', 'Lab Services')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Services Management</h4>
                    <!-- Temporarily remove permission check for debugging -->
                    <a href="{{ route('backend.lab-services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                    <!-- @can('create_lab_services')
                    <a href="{{ route('backend.lab-services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                    @endcan -->
                </div>
                <div class="card-body">
                    <!-- Simple table without DataTables for now -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="services-simple-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Lab</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($allServices))
                                    @foreach($allServices as $service)
                                        <tr>
                                            <td>{{ $service->id }}</td>
                                            <td>{{ $service->name }}</td>
                                            <td>{{ $service->description ? Str::limit($service->description, 50) : 'N/A' }}</td>
                                            <td>{{ $service->category ? $service->category->name : 'N/A' }}</td>
                                            <td>{{ $service->lab ? $service->lab->name : 'N/A' }}</td>
                                            <td>${{ number_format($service->price, 2) }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" 
                                                           data-id="{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('backend.lab-services.edit', $service->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                            data-url="{{ route('backend.lab-services.destroy', $service->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">No services found</td>
                                    </tr>
                                @endif
                            </tbody>
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
    console.log('Document ready, lab services page loaded');
    
    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        var id = $(this).data('id');
        var status = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{{ url("app/lab-services/update-status") }}/' + id,
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
        
        if (confirm('Are you sure you want to delete this service?')) {
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
                    toastr.error(xhr.responseJSON.message || 'Failed to delete service');
                }
            });
        }
    });
});
</script>
@endpush
