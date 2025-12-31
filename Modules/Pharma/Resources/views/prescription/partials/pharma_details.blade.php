<div class="bg-gray-900 border rounded mb-4">
    <div class="p-4">
        <div class="d-flex align-items-start gap-4">
            <img src="{{ $patient->profile_image }}" class="rounded-circle border avatar-80" alt="Patient Image">
            <div class="flex-grow-1">
                <h4 class="mb-2 fw-bold">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <div class="d-flex align-items-center text-body gap-2">
                        <i class="ph ph-phone"></i>
                        <span class="fw-semibold fs-6">{{ $patient->mobile }}</span>
                    </div>
                    <div class="d-flex align-items-center text-body gap-2">
                        <i class="ph ph-envelope"></i>
                        <span class="fw-semibold fs-6">{{ $patient->email ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="d-inline-flex align-items-center gap-2 bg-primary p-2 rounded">
                    <i class="ph ph-pill text-white fs-6"></i>
                    <span class="fw-bold text-white">{{ $totalMedicinePrice }}</span>
                </div>
            </div>

            <!-- Status Section Moved Here -->
            <div class="ms-auto">
                <div class="d-flex flex-column gap-5">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.payment_status') }}
                        </div>
                        @php
                            $appointment = $encounter->appointment;
                            $paymentStatusLabel = '-';
                            $paymentStatusValue = 0;

                            if ($appointment->status === 'cancelled' && $appointment->advance_paid_amount != 0) {
                                $paymentStatusClass = 'bg-warning-subtle';
                                $paymentStatusText = 'Advance Refund';
                            } elseif (
                                $appointment->status === 'cancelled' &&
                                optional($appointment->appointmenttransaction)->payment_status == 1
                            ) {
                                $paymentStatusClass = 'bg-success-subtle';
                                $paymentStatusText = 'Refunded';
                            } elseif ($appointment->status === 'cancelled') {
                                $paymentStatusClass = 'bg-secondary-subtle';
                                $paymentStatusText = '--';
                            } elseif (optional($appointment->appointmenttransaction)->payment_status == 1) {
                                $paymentStatusClass = 'bg-success-subtle';
                                $paymentStatusText = 'Completed';
                                $paymentStatusValue = 1;
                            } else {
                                $paymentStatusClass = 'bg-warning-subtle';
                                $paymentStatusText = 'Pending';
                            }
                        @endphp
                        <span
                            class="badge fs-6 fw-semibold {{ $paymentStatusClass }} px-3 py-2">{{ $paymentStatusText }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.encounter_status') }}
                        </div>
                        @if ($encounter->status == 1)
                            <span class="badge fs-6 fw-semibold bg-warning-subtle px-3 py-2">
                                {{ __('pharma::messages.open') }}

                            </span>
                        @else
                            <span class="badge fs-6 fw-semibold bg-success-subtle px-3 py-2">
                                {{ __('pharma::messages.completed') }}

                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-900 border rounded mb-4">
    <div class="p-4 border-bottom">
        <h5 class="mb-0 fw-semibold fs-4">{{ __('pharma::messages.appointment_details') }}</h5>
    </div>
    <div class="p-4">
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $appointment->cliniccenter?->file_url ?? asset('images/default-clinic.png') }}"
                        class="rounded-circle border avatar-60" alt="Clinic Image">
                    <div>
                        <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.clinic') }}</div>
                        <div class="fw-normal fs-6">{{ $appointment->cliniccenter?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            @if ($pharma)
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $pharma->profile_image ?? asset('images/default-pharma.png') }}"
                            class="rounded-circle border avatar-60" alt="Pharma Image">
                        <div>
                            <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.pharma_name') }}
                            </div>
                            <div class="fw-normal fs-6">{{ $pharma->first_name }} {{ $pharma->last_name }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $appointment->clinicservice?->file_url ?? asset('images/default-service.png') }}"
                        class="rounded-circle border avatar-60" alt="Service Image">
                    <div>
                        <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.service') }}</div>
                        <div class="fw-normal fs-6">{{ $appointment->clinicservice?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $appointment->doctor?->profile_image ?? asset('images/default-doctor.png') }}"
                        class="rounded-circle border avatar-60" alt="Doctor Image">
                    <div>
                        <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.doctor_name') }}</div>
                        <div class="fw-normal fs-6">
                            {{ $appointment->doctor?->first_name . ' ' . $appointment->doctor?->last_name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointment Date/Time with Icon -->
            <div class="col-md-6 col-lg-4">
                <div class="d-flex flex-column align-items-start">
                    <div class="fw-semibold mb-1 fs-6 heading-color">{{ __('pharma::messages.appointment_date_time') }}
                    </div>
                    <div class="fw-normal fs-6">
                        {{ formatDateTime($appointment->start_date_time, $datetimeFormat ?? 'Y-m-d H:i') }}</div>
                </div>
            </div>

            <!-- Prescription Date/Time with Icon -->
            <div class="col-md-6 col-lg-4">
                <div class="d-flex flex-column align-items-start">
                    <div class="fw-semibold mb-1 fs-6 heading-color">
                        {{ __('pharma::messages.prescription_date_time') }}</div>
                    <div class="fw-normal fs-6">
                        {{ formatDateTime($encounter->encounter_date, $datetimeFormat ?? 'Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
