@can('view_medical_certificate')
<a href="{{ route('backend.medical-certificates.show', $data->id) }}" class="btn btn-sm btn-info" title="{{ __('messages.view') }}">
    <i class="fas fa-eye"></i>
</a>
@endcan

@can('edit_medical_certificate')
<a href="{{ route('backend.medical-certificates.edit', $data->id) }}" class="btn btn-sm btn-warning" title="{{ __('messages.edit') }}">
    <i class="fas fa-edit"></i>
</a>
@endcan

@can('delete_medical_certificate')
<a href="javascript:void(0)" onclick="deleteItem('{{ route('backend.medical-certificates.destroy', $data->id) }}')" class="btn btn-sm btn-danger" title="{{ __('messages.delete') }}">
    <i class="fas fa-trash"></i>
</a>
@endcan

@can('print_medical_certificate')
<a href="{{ route('backend.medical-certificates.print', $data->id) }}" class="btn btn-sm btn-primary" title="{{ __('messages.print') }}">
    <i class="fas fa-print"></i>
</a>
@endcan
