<div class="text-end d-flex gap-3 align-items-center">
    @if(auth()->user()->hasRole('vendor'))
      <span class="px-2">-</span>
    @elseif($data['total_pay'] > 0)
      <span  class="fs-4 text-primary border-0 bg-transparent cursor-pointer"  data-crud-id="{{ $data->id }}" title="{{__('Payout')}}" data-bs-toggle="tooltip"><i class="ph ph-money"></i></span>
    @else
      <span  class="px-2">-</span>
    @endif
</div>



