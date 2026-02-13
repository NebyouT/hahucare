<div class="d-flex gap-3 align-items-center">

    <img src="{{ $data->clinic->file_url ?? '' }}" alt="{{ __('pharma::messages.pharma_avatar') }}"
        class="avatar avatar-40 rounded-pill">
    <div class="text-start">
        <h6 class="m-0">{{ $data->clinic->name ?? '--' }}</h6>
        <span>{{ $data->clinic->email ?? '--' }}</span>
    </div>
</div>
