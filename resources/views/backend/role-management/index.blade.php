@extends('backend.layouts.app')

@section('title')
    Role Management
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ph ph-shield-check me-2"></i>
                    Role & Permission Management
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                        <i class="ph ph-plus me-1"></i>
                        Create New Role
                    </button>
                    <button class="btn btn-info" onclick="refreshStats()">
                        <i class="ph ph-chart-bar me-1"></i>
                        Statistics
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Role Statistics -->
                <div class="row mb-4" id="roleStats">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 id="totalRoles">{{ $roles->count() }}</h3>
                                <p class="mb-0">Total Roles</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 id="activeRoles">{{ $roles->where('is_active', true)->count() }}</h3>
                                <p class="mb-0">Active Roles</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 id="totalPermissions">{{ $permissions->flatten()->count() }}</h3>
                                <p class="mb-0">Total Permissions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 id="totalUsers">{{ \App\Models\User::count() }}</h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles List -->
                <div class="table-responsive">
                    <table class="table table-striped" id="rolesTable">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Users Count</th>
                                <th>Permissions Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr id="role-{{ $role->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="role-icon me-2">
                                            @if($role->name === 'admin')
                                                <i class="ph ph-crown text-warning"></i>
                                            @elseif($role->name === 'doctor')
                                                <i class="ph ph-stethoscope text-primary"></i>
                                            @elseif($role->name === 'receptionist')
                                                <i class="ph ph-user-circle-gear text-info"></i>
                                            @else
                                                <i class="ph ph-user text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ ucfirst($role->title ?? $role->name) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $role->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $role->users->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="roleStatus{{ $role->id }}" 
                                               {{ ($role->is_active ?? true) ? 'checked' : '' }}
                                               {{ $role->is_fixed ? 'disabled' : '' }}
                                               onchange="toggleRoleStatus({{ $role->id }})">
                                        <label class="form-check-label" for="roleStatus{{ $role->id }}">
                                            {{ ($role->is_active ?? true) ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary edit-permissions-btn" 
                                                data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->name }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#permissionsModal">
                                            <i class="ph ph-gear-six"></i>
                                            Permissions
                                        </button>
                                        @if(!$role->is_fixed)
                                        <button class="btn btn-sm btn-outline-danger delete-role-btn" 
                                                data-role-id="{{ $role->id }}"
                                                @if($role->users->count() > 0) disabled title="Has assigned users" @endif>
                                            <i class="ph ph-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRoleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="roleName" name="name" required>
                        <div class="form-text">Use lowercase with underscores (e.g., custom_manager)</div>
                    </div>
                    <div class="mb-3">
                        <label for="roleTitle" class="form-label">Display Title</label>
                        <input type="text" class="form-control" id="roleTitle" name="title" required>
                        <div class="form-text">Human-readable title (e.g., Custom Manager)</div>
                    </div>
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions: <span id="currentRoleName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="permissionsForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills" id="permission-tabs" role="tablist">
                                @foreach($menuItems as $key => $item)
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                        id="tab-{{ $key }}" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#pane-{{ $key }}" 
                                        type="button" 
                                        role="tab">
                                    {{ $item['label'] }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content" id="permission-content">
                                @foreach($menuItems as $key => $item)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                     id="pane-{{ $key }}" 
                                     role="tabpanel">
                                    <h6 class="mb-3">{{ $item['label'] }} Permissions</h6>
                                    <div class="row">
                                        @foreach($item['permissions'] as $permission)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox" 
                                                       id="perm-{{ $permission }}" 
                                                       name="permissions[]" 
                                                       value="{{ $permission }}">
                                                <label class="form-check-label" for="perm-{{ $permission }}">
                                                    {{ ucwords(str_replace('_', ' ', $permission)) }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <hr>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="selectAllInTab('{{ $key }}')">
                                            Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="deselectAllInTab('{{ $key }}')">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
let currentRoleId = null;

// Create Role
document.getElementById('createRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("backend.role-management.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});

// Edit Permissions - Event Listener
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-permissions-btn')) {
        const btn = e.target.closest('.edit-permissions-btn');
        const roleId = btn.dataset.roleId;
        const roleName = btn.dataset.roleName;
        editPermissions(roleId, roleName);
    }
    
    if (e.target.closest('.delete-role-btn')) {
        const btn = e.target.closest('.delete-role-btn');
        const roleId = btn.dataset.roleId;
        if (!btn.disabled) {
            deleteRole(roleId);
        }
    }
});

// Edit Permissions
function editPermissions(roleId, roleName) {
    currentRoleId = roleId;
    document.getElementById('currentRoleName').textContent = roleName;
    
    // Clear all checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Load current permissions
    fetch(`{{ url('/app/role-management') }}/${roleId}/permissions`)
        .then(response => response.json())
        .then(data => {
            if (data.permissions) {
                data.permissions.forEach(permission => {
                    const checkbox = document.getElementById(`perm-${permission}`);
                    if (checkbox) checkbox.checked = true;
                });
            }
        });
}

// Save Permissions
document.getElementById('permissionsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`{{ url('/app/role-management') }}/${currentRoleId}/permissions`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('permissionsModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    });
});

// Toggle Role Status
function toggleRoleStatus(roleId) {
    fetch(`{{ url('/app/role-management') }}/${roleId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error: ' + (data.message || 'Unknown error'));
            location.reload();
        }
    });
}

// Delete Role
function deleteRole(roleId) {
    if (!confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        return;
    }
    
    fetch(`{{ url('/app/role-management') }}/${roleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`role-${roleId}`).remove();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    });
}

// Select/Deselect All in Tab
function selectAllInTab(tabKey) {
    document.querySelectorAll(`#pane-${tabKey} .permission-checkbox`).forEach(cb => cb.checked = true);
}

function deselectAllInTab(tabKey) {
    document.querySelectorAll(`#pane-${tabKey} .permission-checkbox`).forEach(cb => cb.checked = false);
}

// Refresh Statistics
function refreshStats() {
    fetch('{{ route("backend.role-management.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalRoles').textContent = data.total_roles;
            document.getElementById('activeRoles').textContent = data.active_roles;
            document.getElementById('totalPermissions').textContent = data.total_permissions;
        });
}
</script>
@endpush

@endsection
