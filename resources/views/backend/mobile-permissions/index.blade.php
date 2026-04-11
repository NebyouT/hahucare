@extends('backend.layouts.app')

@section('title')
    Mobile App Permissions
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Header Card --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title mb-0">
                    <i class="ph ph-device-mobile me-2"></i>
                    Mobile App Permissions
                </h4>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-success" id="btnSync" onclick="syncPermissions()">
                        <i class="ph ph-arrows-clockwise me-1"></i>
                        Sync All (Run Seeder)
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPermModal">
                        <i class="ph ph-plus me-1"></i>
                        Add Permission
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row mb-3">
            <div class="col-md-3 col-6">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0">{{ $totalMobilePerms }}</h3>
                        <small>Mobile Permissions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0">{{ $roles->count() }}</h3>
                        <small>Roles Managed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-info text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0">{{ count($groups) }}</h3>
                        <small>Feature Groups</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card {{ $missingPerms->count() > 0 ? 'bg-danger' : 'bg-secondary' }} text-white">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-0">{{ $missingPerms->count() }}</h3>
                        <small>Missing in DB</small>
                    </div>
                </div>
            </div>
        </div>

        @if($missingPerms->count() > 0)
        <div class="alert alert-danger d-flex align-items-center mb-3">
            <i class="ph ph-warning-circle me-2 fs-4"></i>
            <div>
                <strong>{{ $missingPerms->count() }} mobile permissions are missing from the database.</strong>
                Click <strong>Sync All</strong> to create them.
                <br><small class="text-muted">Missing: {{ $missingPerms->implode(', ') }}</small>
            </div>
        </div>
        @endif

        {{-- Permission Matrix --}}
        @foreach($groups as $groupName => $permissions)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ph ph-folder-simple me-2"></i>
                    {{ $groupName }}
                </h5>
                <span class="badge bg-secondary">{{ count($permissions) }} permissions</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 240px;">Permission</th>
                                @foreach($roles as $role)
                                <th class="text-center" style="min-width: 110px;">
                                    <span class="d-block fw-bold">{{ ucfirst($role->title ?? $role->name) }}</span>
                                    <small class="text-muted">{{ $role->name }}</small>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $perm)
                            <tr>
                                <td>
                                    <code class="text-dark">{{ $perm }}</code>
                                    @php
                                        $existsInDb = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
                                    @endphp
                                    @if(!$existsInDb)
                                        <span class="badge bg-danger ms-1">Not in DB</span>
                                    @endif
                                </td>
                                @foreach($roles as $role)
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input perm-toggle"
                                               type="checkbox"
                                               data-role-id="{{ $role->id }}"
                                               data-role-name="{{ $role->name }}"
                                               data-perm="{{ $perm }}"
                                               id="toggle-{{ $role->id }}-{{ $perm }}"
                                               {{ $role->hasPermissionTo($perm) ? 'checked' : '' }}
                                               {{ !$existsInDb ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        {{-- Group footer with Select All / Deselect All per role --}}
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold text-end">Quick Actions:</td>
                                @foreach($roles as $role)
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success btn-sm px-1 py-0"
                                                title="Grant all {{ $groupName }}"
                                                onclick="toggleGroup('{{ $role->id }}', '{{ $groupName }}', true)">
                                            <i class="ph ph-check-circle"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm px-1 py-0"
                                                title="Revoke all {{ $groupName }}"
                                                onclick="toggleGroup('{{ $role->id }}', '{{ $groupName }}', false)">
                                            <i class="ph ph-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Save Bar --}}
        <div class="card border-primary" id="saveBar" style="display:none; position:sticky; bottom:1rem; z-index:100;">
            <div class="card-body d-flex justify-content-between align-items-center py-2">
                <span class="text-primary fw-bold">
                    <i class="ph ph-info me-1"></i>
                    You have unsaved changes for <span id="changedRolesCount">0</span> role(s)
                </span>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary btn-sm" onclick="discardChanges()">
                        <i class="ph ph-x me-1"></i> Discard
                    </button>
                    <button class="btn btn-primary" onclick="saveAllChanges()" id="btnSave">
                        <i class="ph ph-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Permission Modal --}}
<div class="modal fade" id="addPermModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ph ph-plus-circle me-2"></i>
                    Add Mobile Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPermForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="permName" class="form-label">Permission Name</label>
                        <div class="input-group">
                            <span class="input-group-text">mobile_</span>
                            <input type="text" class="form-control" id="permName" name="name"
                                   placeholder="e.g. view_lab_results" required
                                   pattern="[a-z0-9_]+"
                                   title="Lowercase letters, numbers, and underscores only">
                        </div>
                        <div class="form-text">
                            The <code>mobile_</code> prefix will be added automatically.
                            Use lowercase with underscores.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-plus me-1"></i> Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
// Track pending changes per role
let pendingChanges = {};

// Collect all toggle changes
document.querySelectorAll('.perm-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const roleId = this.dataset.roleId;
        const roleName = this.dataset.roleName;
        const perm = this.dataset.perm;

        if (!pendingChanges[roleId]) {
            pendingChanges[roleId] = { name: roleName, perms: {} };
        }
        pendingChanges[roleId].perms[perm] = this.checked;

        updateSaveBar();
    });
});

function updateSaveBar() {
    const count = Object.keys(pendingChanges).length;
    document.getElementById('changedRolesCount').textContent = count;
    document.getElementById('saveBar').style.display = count > 0 ? 'block' : 'none';
}

function discardChanges() {
    location.reload();
}

// Save all pending changes
function saveAllChanges() {
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const roleIds = Object.keys(pendingChanges);
    let completed = 0;
    let errors = [];

    roleIds.forEach(roleId => {
        // Collect ALL current checkbox states for this role
        const checkboxes = document.querySelectorAll(`.perm-toggle[data-role-id="${roleId}"]`);
        const permissions = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                permissions.push(cb.dataset.perm);
            }
        });

        fetch(`{{ url('/app/mobile-permissions') }}/${roleId}/update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ permissions: permissions })
        })
        .then(r => r.json())
        .then(data => {
            completed++;
            if (!data.success) {
                errors.push(pendingChanges[roleId].name + ': ' + (data.message || 'Unknown error'));
            }
            if (completed === roleIds.length) {
                if (errors.length > 0) {
                    alert('Some updates failed:\n' + errors.join('\n'));
                } else {
                    showToast('All mobile permissions saved successfully!', 'success');
                }
                pendingChanges = {};
                updateSaveBar();
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-floppy-disk me-1"></i> Save Changes';
            }
        })
        .catch(err => {
            completed++;
            errors.push(pendingChanges[roleId].name + ': Network error');
            if (completed === roleIds.length) {
                alert('Errors:\n' + errors.join('\n'));
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-floppy-disk me-1"></i> Save Changes';
            }
        });
    });
}

// Toggle all permissions in a group for a role
function toggleGroup(roleId, groupName, state) {
    const card = [...document.querySelectorAll('.card-title')].find(el => el.textContent.trim().includes(groupName));
    if (!card) return;

    const cardBody = card.closest('.card');
    const checkboxes = cardBody.querySelectorAll(`.perm-toggle[data-role-id="${roleId}"]`);

    checkboxes.forEach(cb => {
        if (!cb.disabled && cb.checked !== state) {
            cb.checked = state;
            cb.dispatchEvent(new Event('change'));
        }
    });
}

// Sync permissions (run seeder)
function syncPermissions() {
    const btn = document.getElementById('btnSync');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

    fetch('{{ route("backend.mobile-permissions.sync") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Mobile permissions synced! Reloading...', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Sync failed: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-arrows-clockwise me-1"></i> Sync All (Run Seeder)';
        }
    })
    .catch(() => {
        alert('Network error during sync');
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-arrows-clockwise me-1"></i> Sync All (Run Seeder)';
    });
}

// Add new permission
document.getElementById('addPermForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const nameInput = document.getElementById('permName');
    const name = nameInput.value.trim();

    if (!name) return;

    fetch('{{ route("backend.mobile-permissions.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: name })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Permission "' + data.name + '" created! Reloading...', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => alert('Network error'));
});

// Simple toast notification
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed shadow`;
    toast.style.cssText = 'top:1rem;right:1rem;z-index:9999;min-width:300px;';
    toast.innerHTML = '<i class="ph ph-' + (type === 'success' ? 'check-circle' : 'x-circle') + ' me-2"></i>' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush

@endsection
