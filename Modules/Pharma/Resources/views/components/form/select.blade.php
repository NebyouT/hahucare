<select
    name="{{ $name }}"
    id="{{ $id ?? $name }}"
    class="{{ $class ?? 'form-control select2' }}"
    @if(!empty($required)) required @endif
    @if(!empty($disabled)) disabled @endif
    @if(!empty($attributes['multiple']) || str_ends_with($name, '[]')) multiple @endif
>
    <option value="">{{ $placeholder ?? __('pharma::messages.select_option') }}</option>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" 
            {{ (is_array($selected) && in_array($value, $selected)) ? 'selected' : ($selected == $value ? 'selected' : '') }}>
            {{ $label }}
        </option>
    @endforeach
</select>