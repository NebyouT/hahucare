@extends('backend.layouts.app')

@section('title', 'Lab Equipment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Equipment Management</h4>
                    @can('create_lab_equipment')
                    <a href="{{ route('backend.lab-equipment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Equipment
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="lab-equipment-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Equipment Code</th>
                                    <th>Equipment Name</th>
                                    <th>Manufacturer</th>
                                    <th>Status</th>
                                    <th>Maintenance</th>
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
    var table = $('#lab-equipment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("backend.lab-equipment.index_data") }}',
        columns: [
            { data: 'equipment_code', name: 'equipment_code' },
            { data: 'equipment_name', name: 'equipment_name' },
            { data: 'manufacturer', name: 'manufacturer' },
            { data: 'status_badge', name: 'status' },
            { data: 'maintenance_status', name: 'next_maintenance_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Delete button
    $(document).on('click', '.delete-btn', function() {
        var url = $(this).data('url');
        
        if (confirm('Are you sure you want to delete this equipment?')) {
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
                    toastr.error('Failed to delete equipment');
                }
            });
        }
    });
});
</script>
@endpush
