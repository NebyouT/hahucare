<div class="d-flex gap-3 align-items-center">
    <img src="{{ $data->medicine->supplier->profile_image ?? default_user_avatar() }}" alt="{{ __('pharma::messages.pharma_avatar') }}" class="avatar avatar-40 rounded-pill">
    <div class="text-start">
      <h6 class="m-0">{{ $data->medicine->supplier->full_name ?? default_user_name() }}</h6>
      <span>{{ $data->medicine->supplier->email ?? '--' }}</span>
    </div>
  </div>
  