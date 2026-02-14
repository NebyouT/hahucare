<!-- Patient Details Card -->
<div class="card mb-5">
    <div class="card-body p-5">
        <div class="row align-items-center gy-3 mb-3">
            <div class="col-lg-2 col-md-3">
                <img src="{{ $pharmaDetail->profile_image }}" alt="Patient Avatar"
                    class="rounded-circle avatar-80 object-cover">
            </div>
            <div class="col-lg-10 col-md-9">
                <h5 class="font-size-18">{{ $pharmaDetail->first_name }} {{ $pharmaDetail->last_name }}</h5>
                <div class="d-flex flex-wrap gap-3">
                    <small class="d-flex align-items-center gap-lg-3 gap-2">
                        <i class="ph ph-envelope h5 m-0"></i>
                        <span
                            class="text-secondary text-decoration-underline fw-medium font-size-16">{{ $pharmaDetail->email }}</span>
                    </small>
                    <small class="d-flex align-items-center gap-lg-3 gap-2">
                        <i class="ph ph-phone h5 m-0"></i>
                        <span
                            class="text-primary text-decoration-underline fw-medium font-size-16">{{ $pharmaDetail->mobile }}</span>
                    </small>
                </div>
            </div>
        </div>
        <div class="row gy-2">
            <div class="col-lg-2 col-md-3">
                <span>{{ __('pharma::messages.gender') }}:</span>
                <p class="mt-2 h6 mb-0">{{ $pharmaDetail->gender }}</p>
            </div>
            <div class="col-lg-2 col-md-3">
                <span>{{ __('pharma::messages.dob') }}:</span>
                <p class="mt-2 h6 mb-0">{{ $pharmaDetail->date_of_birth }}</p>
            </div>
        </div>
    </div>
</div>


<h5>{{ __('pharma::messages.clinic_details') }}</h5>
<div class="card">
    <div class="card-body p-5">
        <div class="row align-items-center">
            <div class="col-lg-2 col-md-3">
                <img src="{{ $pharmaDetail->clinic->file_url }}" alt="Clinic Image"
                    class="rounded avatar-80 object-cover">
            </div>
            <div class="col-lg-10 col-md-9">
                <h5 class="font-size-18">{{ $pharmaDetail->clinic->name }}</h5>

                <div class="d-flex flex-wrap gap-3">
                    <small class="d-flex align-items-center gap-lg-3 gap-2">
                        <i class="ph ph-envelope h5 m-0"></i>
                        <span
                            class="text-secondary text-decoration-underline fw-medium font-size-16">{{ $pharmaDetail->clinic->email }}</span>
                    </small>
                    <small class="d-flex align-items-center gap-lg-3 gap-2">
                        <i class="ph ph-phone h5 m-0"></i>
                        <span
                            class="text-primary text-decoration-underline fw-medium font-size-16">{{ $pharmaDetail->clinic->contact_number }}</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
