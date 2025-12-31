<div class="text-end d-flex gap-3 align-items-center">
  @if($data['total_pay'] > 0)
    <button type="button"
      class="btn text-primary p-0 fs-5 pharma-payout-btn"
      data-id="{{ $data->id }}"
      title="{{ __('Payout') }}"
      data-bs-toggle="tooltip"
    >
      <i class="ph ph-money"></i>
    </button>
  @else
    <span class="px-2">-</span>
  @endif
</div>
