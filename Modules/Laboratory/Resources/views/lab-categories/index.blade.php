@extends('backend.layouts.app')

@section('title', 'Lab Test Categories')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Test Categories</h4>
                    @can('create_lab_categories')
                    <a href="{{ route('backend.lab-categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    
                    <!-- Simple table without DataTables for now -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="categories-simple-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Tests Count</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($allCategories))
                                    @foreach($allCategories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td>{{ $category->slug }}</td>
                                            <td>{{ $category->description ?? 'N/A' }}</td>
                                            <td>{{ $category->lab_tests_count }}</td>
                                            <td>{{ $category->sort_order ?? 'N/A' }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" 
                                                           data-id="{{ $category->id }}" {{ $category->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('backend.lab-categories.edit', $category->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                            data-url="{{ route('backend.lab-categories.destroy', $category->id) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">No categories found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- DataTables table (hidden for now) -->
                    <div class="table-responsive" style="display: none;">
                        <table id="lab-categories-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Tests Count</th>
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
    console.log('Document ready, lab categories page loaded');
    
    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        var id = $(this).data('id');
        var status = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{{ url("app/lab-categories/update-status") }}/' + id,
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
        
        if (confirm('Are you sure you want to delete this category?')) {
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
                    toastr.error(xhr.responseJSON.message || 'Failed to delete category');
                }
            });
        }
    });
});
</script>
@endpush
