@extends('backend.layouts.app')

@section('title')
   {{ __($module_title) }}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title mb-0">{{ __('messages.permission_role') }}</h4>
                </div>
                <div>
                    <x-backend.section-header>
                        <div>

                        </div>
                        <x-slot name="toolbar">


                            <div class="input-group flex-nowrap">
                            </div>

                           
                        </x-slot>
                    </x-backend.section-header>


                </div>
            </div>
            <div class="card-body">
                @foreach ($roles as $role)
             
                @if($role->name !== 'admin' && $role->name !== 'shopmanager')
                {{ html()->form('post', route('backend.permission-role.store', $role->id))->open() }}

                @if($role->name=='vendor' && multiVendor()==0)
           
                @else
                    @if($role->name != 'pharma')
                        <div class="permission-collapse border rounded p-3 mb-3" id="permission_{{$role->id}}">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6>{{ ucfirst($role->title) }}</h6>
                                <div class="toggle-btn-groups">
                                    @if($role->is_fixed ==0)
                                    <button class="btn btn-danger" type="button" onclick="delete_role({{$role->id}})">
                                    {{ __('messages.delete')}}
                                    </button>
                                    @endif
                                    {{-- <button class="btn btn-gray ms-2" type="button" onclick="reset_permission({{$role->id}})">
                                        Default Permission
                                    </button> --}}
                                    <button class="btn btn-primary ms-2" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseBox1_{{$role->id}}" aria-expanded="false"
                                        aria-controls="collapseExample_{{$role->id}}">
                                        {{ __('messages.permission')}}
                                    </button>
                                </div>
                            </div>
                            <div class="collapse pt-3" id="collapseBox1_{{$role->id}}">
                                <div class="table-responsive">
                                <table class="table table-condensed table-striped mb-0">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th>{{ __('messages.modules')}}</th>
                                            <th>{{ __('messages.view')}}</th>
                                            <th>{{ __('messages.add')}}</th>
                                            <th>{{ __('messages.edit')}}</th>
                                            <th>{{ __('messages.delete')}}</th>
                                            <th class="text-end">{{ html()->submit(__('messages.save'))->class('btn btn-md btn-secondary') }}
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($groupedPermissions as $moduleName => $modulePermissions)
                                            @php
                                                // Extract specific permissions for this module
                                                $viewPermission = $modulePermissions->where('name', 'like', 'view_%')->first();
                                                $addPermission = $modulePermissions->where('name', 'like', 'add_%')->first();
                                                $editPermission = $modulePermissions->where('name', 'like', 'edit_%')->first();
                                                $deletePermission = $modulePermissions->where('name', 'like', 'delete_%')->first();
                                                
                                                // Get other permissions (non-CRUD)
                                                $otherPermissions = $modulePermissions->whereNotIn('name', 
                                                    $modulePermissions->whereIn('name', ['view_%', 'add_%', 'edit_%', 'delete_%'])->pluck('name')->toArray()
                                                );
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $moduleName }}</strong></td>
                                                <td>
                                                    @if($viewPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$viewPermission->name}}"
                                                                name="permission[{{$viewPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $viewPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($addPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$addPermission->name}}"
                                                                name="permission[{{$addPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $addPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($editPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$editPermission->name}}"
                                                                name="permission[{{$editPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $editPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($deletePermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$deletePermission->name}}"
                                                                name="permission[{{$deletePermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $deletePermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>

                                                @if($otherPermissions->count() > 0)
                                                    <td class="text-end">
                                                        <a data-bs-toggle="collapse" data-bs-target="#demo_{{str_replace(' ', '_', $moduleName)}}_{{$role->id}}" class="accordion-toggle btn btn-primary btn-xs">
                                                            <i class="fa-solid fa-chevron-down me-2"></i>More
                                                        </a>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            </tr>

                                            @if($otherPermissions->count() > 0)
                                                <tr>
                                                    <td colspan="12" class="hiddenRow">
                                                        <div class="accordian-body collapse" id="demo_{{str_replace(' ', '_', $moduleName)}}_{{$role->id}}">
                                                            <table class="table table-striped mb-0">
                                                                <tbody>
                                                                    @foreach($otherPermissions as $permission)
                                                                        <tr>
                                                                            <td class="d-flex justify-content-center">
                                                                                <div class="form-check form-switch">
                                                                                    <input type="checkbox"
                                                                                        id="role-{{$role->name}}-permission-{{$permission->name}}"
                                                                                        name="permission[{{$permission->name}}][]"
                                                                                        value="{{$role->name}}" class="form-check-input"
                                                                                        {{ (AuthHelper::checkRolePermission($role, $permission->name)) ? 'checked' : '' }}>
                                                                                    <label for="role-{{$role->name}}-permission-{{$permission->name}}" class="form-check-label ms-2">
                                                                                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                                                    </label>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>

                                </table>
                                </div>
                            </div>
                        </div>
                    @else
                        @if($role->name == 'pharma')
                        <div class="permission-collapse border rounded p-3 mb-3" id="permission_{{$role->id}}">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6>{{ ucfirst($role->title) }}</h6>
                                <div class="toggle-btn-groups">
                                    @if($role->is_fixed ==0)
                                    <button class="btn btn-danger" type="button" onclick="delete_role({{$role->id}})">
                                    {{ __('messages.delete')}}
                                    </button>
                                    @endif
                                    {{-- <button class="btn btn-gray ms-2" type="button" onclick="reset_permission({{$role->id}})">
                                        Default Permission
                                    </button> --}}
                                    <button class="btn btn-primary ms-2" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseBox1_{{$role->id}}" aria-expanded="false"
                                        aria-controls="collapseExample_{{$role->id}}">
                                        {{ __('messages.permission')}}
                                    </button>
                                </div>
                            </div>
                            <div class="collapse pt-3" id="collapseBox1_{{$role->id}}">
                                <div class="table-responsive">
                                <table class="table table-condensed table-striped mb-0">
                                    <thead class="sticky-top">
                                        <tr>
                                            <th>{{ __('messages.modules')}}</th>
                                            <th>{{ __('messages.view')}}</th>
                                            <th>{{ __('messages.add')}}</th>
                                            <th>{{ __('messages.edit')}}</th>
                                            <th>{{ __('messages.delete')}}</th>
                                            <th class="text-end">{{ html()->submit(__('messages.save'))->class('btn btn-md btn-secondary') }}
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($groupedPermissions as $moduleName => $modulePermissions)
                                            @php
                                                // Extract specific permissions for this module
                                                $viewPermission = $modulePermissions->where('name', 'like', 'view_%')->first();
                                                $addPermission = $modulePermissions->where('name', 'like', 'add_%')->first();
                                                $editPermission = $modulePermissions->where('name', 'like', 'edit_%')->first();
                                                $deletePermission = $modulePermissions->where('name', 'like', 'delete_%')->first();
                                                
                                                // Get other permissions (non-CRUD)
                                                $otherPermissions = $modulePermissions->whereNotIn('name', 
                                                    $modulePermissions->whereIn('name', ['view_%', 'add_%', 'edit_%', 'delete_%'])->pluck('name')->toArray()
                                                );
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $moduleName }}</strong></td>
                                                <td>
                                                    @if($viewPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$viewPermission->name}}_pharma"
                                                                name="permission[{{$viewPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $viewPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($addPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$addPermission->name}}_pharma"
                                                                name="permission[{{$addPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $addPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($editPermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$editPermission->name}}_pharma"
                                                                name="permission[{{$editPermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $editPermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($deletePermission)
                                                        <span>
                                                            <input type="checkbox"
                                                                id="role-{{$role->name}}-permission-{{$deletePermission->name}}_pharma"
                                                                name="permission[{{$deletePermission->name}}][]"
                                                                value="{{$role->name}}" class="form-check-input"
                                                                {{ (AuthHelper::checkRolePermission($role, $deletePermission->name)) ? 'checked' : '' }}>
                                                        </span>
                                                    @endif
                                                </td>

                                                @if($otherPermissions->count() > 0)
                                                    <td class="text-end">
                                                        <a data-bs-toggle="collapse" data-bs-target="#demo_{{str_replace(' ', '_', $moduleName)}}_{{$role->id}}_pharma" class="accordion-toggle btn btn-primary btn-xs">
                                                            <i class="fa-solid fa-chevron-down me-2"></i>More
                                                        </a>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                            </tr>

                                            @if($otherPermissions->count() > 0)
                                                <tr>
                                                    <td colspan="12" class="hiddenRow">
                                                        <div class="accordian-body collapse" id="demo_{{str_replace(' ', '_', $moduleName)}}_{{$role->id}}_pharma">
                                                            <table class="table table-striped mb-0">
                                                                <tbody>
                                                                    @foreach($otherPermissions as $permission)
                                                                        <tr>
                                                                            <td class="d-flex justify-content-center">
                                                                                <div class="form-check form-switch">
                                                                                    <input type="checkbox"
                                                                                        id="role-{{$role->name}}-permission-{{$permission->name}}_pharma"
                                                                                        name="permission[{{$permission->name}}][]"
                                                                                        value="{{$role->name}}" class="form-check-input"
                                                                                        {{ (AuthHelper::checkRolePermission($role, $permission->name)) ? 'checked' : '' }}>
                                                                                    <label for="role-{{$role->name}}-permission-{{$permission->name}}_pharma" class="form-check-label ms-2">
                                                                                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                                                    </label>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>

                                </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                @endif
                {{ html()->form()->close() }}

                @endif
                @endforeach




            </div>
        </div>

        <div data-render="app">
            <manage-role-form create-title="{{ __('messages.create') }}  {{ __('page.lbl_role') }}">
            </manage-role-form>

        </div>

    </div>
</div>



<script>
function reset_permission(role_id) {

    var url = "/app/permission-role/reset/" + role_id;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            successSnackbar(response.message);
            window.location.reload();
        },
        error: function(response) {
            alert('error');
        }
    });
}



function delete_role(role_id) {
    var url = "{{ route('backend.role.destroy', ['role' => ':role_id']) }}";
    url = url.replace(':role_id', role_id);

    $.ajax({
        url: url,
        type: 'DELETE',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#permission_' + role_id).hide();
            successSnackbar(response.message);

        },
        error: function(response) {
            alert('error');
        }
    });
}
</script>



@push('after-scripts')
{{-- <script src="{{ mix('js/vue.min.js') }}"></script> --}}
<script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>

@endpush

@endsection
