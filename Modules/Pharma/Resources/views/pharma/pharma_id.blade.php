@if($data !== null)
<div class="d-flex gap-3 align-items-center">
  <img src="{{ $data->profile_image }}" alt="avatar" class="avatar avatar-40 rounded-pill">
  <div class="text-start">
    <h6 class="m-0">{{ $data->first_name }} {{ $data->last_name }}</h6>
    <span>{{ $data->email ?? '--' }}</span>
  </div>
</div>
@else
<p>-</p>
@endif