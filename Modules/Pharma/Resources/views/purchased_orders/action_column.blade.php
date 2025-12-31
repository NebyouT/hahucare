<div class="d-flex gap-3 align-items-center">
 @php
    $isPaymentCompleted = $data->payment_status === config('constant.PAYMENT_STATUS.COMPLETED');
    $isOrderDelivered = $data->order_status === config('constant.ORDER_STATUS.DELIVERED');
@endphp

{{-- Edit Button --}}
@if ((auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('edit_purchased_order')) 
    && !($isPaymentCompleted && $isOrderDelivered))
    <button type="button" class="btn text-success p-0 fs-5 edit-order-medicine-btn" data-id="{{ $data->id }}"
        data-crud-id="{{ $data->id }}" title="{{ __('messages.edit') }}" data-bs-toggle="tooltip">
        <i class="ph ph-pencil-simple-line align-middle"></i>
    </button>
@else
    <span style="width: 1.5rem;"></span>
@endif

{{-- Delete Button --}}
@if ((auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('delete_purchased_order')) 
    && !($isPaymentCompleted && $isOrderDelivered))
    <a href="{{ route('backend.order-medicine.destroy', $data->id) }}"
        id="delete-{{ $module_name }}-{{ $data->id }}" class="btn text-danger p-0 fs-5" data-type="ajax"
        data-method="DELETE" data-token="{{ csrf_token() }}" data-bs-toggle="tooltip"
        title="{{ __('messages.delete') }}"
        data-confirm="{{ __('messages.are_you_sure?', ['form' => '#' . ($data->order_number ?? $data->id), 'module' => __('pharma::messages.purchased_orders')]) }}">
        <i class="ph ph-trash align-middle"></i>
    </a>
@endif

    @if (auth()->user()->hasRole(['admin', 'demo_admin']) || auth()->user()->can('view_purchased_order'))
        <button type="button" class="btn p-0 fs-5 text-primary view-order-medicine-btn" data-id="{{ $data->id }}"
            data-crud-id="{{ $data->id }}" title="{{ __('messages.order') }}" data-bs-toggle="tooltip">
            <i class="ph ph-notepad align-middle"></i>
        </button>
    @endif
</div>
