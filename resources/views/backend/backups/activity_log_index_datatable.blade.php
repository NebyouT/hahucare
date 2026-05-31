@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection

@section('content')
    <div class="table-content mb-3">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="activityLogTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" onclick="filterLogs('all', 'all')">
                    All Logs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="created-tab" data-bs-toggle="tab" data-bs-target="#created" type="button" role="tab" onclick="filterLogs('created', 'all')">
                    <span class="badge bg-success me-1">Created</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="updated-tab" data-bs-toggle="tab" data-bs-target="#updated" type="button" role="tab" onclick="filterLogs('updated', 'all')">
                    <span class="badge bg-primary me-1">Updated</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted" type="button" role="tab" onclick="filterLogs('deleted', 'all')">
                    <span class="badge bg-danger me-1">Deleted</span>
                </button>
            </li>
        </ul>

        <!-- Role Filter Dropdown -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Filter by Role:</label>
                <select class="form-select" id="roleFilter" onchange="filterLogs('all', this.value)">
                    <option value="all">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="demo_admin">Demo Admin</option>
                    <option value="vendor">Clinic Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="user">Patient</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="lab_technician">Lab Technician</option>
                </select>
            </div>
        </div>

        <table id="datatable" class="table table-responsive"></table>
    </div>

    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{ __('messages.lbl_history') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="view-data">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script type="text/javascript">
        let currentEventType = 'all';
        let currentCauserRole = 'all';
        let datatableInstance = null;

        const columns = [{
                data: 'created_at',
                name: 'created_at',
                title: '{{ __('messages.created_at') }}',
                orderable: true,
            },
            {
                data: 'event',
                name: 'event',
                title: 'Action',
                orderable: true,
            },
            {
                data: 'subject_type',
                name: 'subject_type',
                title: '{{ __('messages.subject_type') }}',
                orderable: false,
            },
            {
                data: 'causer',
                name: 'causer',
                title: 'Performed By',
                orderable: false,
            },
            {
                data: 'description',
                name: 'description',
                title: "{{ __('clinic.lbl_description') }}",
                orderable: false,
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                width: '15%',
                visible: false
            },
        ];
        const actionColumn = [{
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: '{{ __('service.lbl_action') }}',
            width: '5%'
        }];
        let finalColumns = [
            ...columns,
            ...actionColumn
        ];

        function filterLogs(eventType, causerRole) {
            currentEventType = eventType;
            currentCauserRole = causerRole;
            
            // Update active tab
            document.querySelectorAll('#activityLogTabs .nav-link').forEach(btn => {
                btn.classList.remove('active');
            });
            if (eventType === 'all') {
                document.getElementById('all-tab').classList.add('active');
            } else {
                document.getElementById(eventType + '-tab').classList.add('active');
            }
            
            // Reload datatable with filters
            if (datatableInstance) {
                datatableInstance.ajax.reload();
            }
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            datatableInstance = initDatatable({
                url: '{{ $dataUrl }}',
                finalColumns,
                orderColumn: [
                    [5, 'desc']
                ],
                advanceFilter: () => {
                    return {
                        event_type: currentEventType,
                        causer_role: currentCauserRole
                    };
                }
            });
        });
    </script>
    <script>
        function getHistory(id) {
            if (id != "") {
                var url = "{{ route($viewUrl, ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#view-data').html(response);
                        $("#staticBackdrop").modal('show');
                    },
                    error: function(response) {
                        alert('error');
                    }
                });
            }
        }

        function confirmRollback(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will revert the changes made in this action. Proceed?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, rollback!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route($rollbackUrl, ':id') }}";
                    url = url.replace(':id', id);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire('Rolled back!', response.message, 'success');
                                if (window.LaravelDataTables && window.LaravelDataTables['datatable']) {
                                    window.LaravelDataTables['datatable'].ajax.reload();
                                } else {
                                    location.reload();
                                }
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var msg = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
