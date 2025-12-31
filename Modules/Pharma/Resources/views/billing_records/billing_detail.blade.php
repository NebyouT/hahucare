@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection
@section('content')


    <style type="text/css" media="print">
        @page :footer {
            display: none !important;
        }

        @page :header {
            display: none !important;
        }

        @page {
            size: landscape;
        }

        /* @page { margin: 0; } */

        .pr-hide {
            display: none;
        }


        .order_table tr td div {
            white-space: normal;
        }


        * {
            -webkit-print-color-adjust: none !important;
            /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: none !important;
            /* Firefox 48 – 96 */
            print-color-adjust: none !important;
            /* Firefox 97+, Safari 15.4+ */
        }
    </style>

    <b-row>
        <b-col sm="12">
            <div id="bill">
                @php
                    use Carbon\Carbon;
                @endphp

                <div class="row pr-hide mb-4">


                    <div class="d-flex justify-content-end align-items-center ">
                        <a class="btn btn-primary" onclick="invoicePrint(this)">
                            <i class="fa-solid fa-download"></i>
                            {{ __('messages.print') }}
                        </a>
                    </div>
                </div>


                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                    <p class="mb-0">{{ __('messages.invoice_id') }}:<span class="text-secondary">
                                            #{{ $billing['id'] ?? '--' }}</span></h3>
                                    <p class="mb-0">
                                        {{ __('messages.payment_status') }}
                                        @if ($billing['prescription_payment_status'] == 1)
                                            <span
                                                class="badge booking-status bg-success-subtle p-2">{{ __('messages.paid') }}</span>
                                        @elseif($billing['prescription_payment_status'] == 0)
                                            <span
                                                class="badge booking-status bg-danger-subtle p-2">{{ __('messages.unpaid') }}</span>
                                        @endif
                                    </p>
                                </div>
                                <p class="mt-1 mb-0">{{ __('messages.date') }}: <span class="font-weight-bold text-dark">
                                        {{ isset($billing['created_at'])
                                            ? \Carbon\Carbon::parse($billing['created_at'])->timezone($timezone)->format($dateformate) .
                                                ' At ' .
                                                \Carbon\Carbon::parse($billing['created_at'])->timezone($timezone)->format($timeformate)
                                            : '-- At --' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gy-3">
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">{{ __('pharma::messages.clinic_info') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="image-block">
                                    <img src="{{ $billing['clinic']['file_url'] ?? '--' }}"
                                        class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $billing['clinic']['name'] ?? '--' }}</h5>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-envelope text-dark"></i>
                                            <u class="text-secondary">{{ $billing['clinic']['email'] ?? '--' }}</u>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-map-pin text-dark"></i>
                                            <span>{{ $billing['clinic']['address'] ?? '--' }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-phone-call text-dark"></i>
                                            <span>{{ $billing['clinic']['contact_number'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">{{ __('messages.doctor_details') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center h-100 gap-3">
                                <div class="image-block">
                                    <img src="{{ $billing['doctor']['profile_image'] ?? '--' }}"
                                        class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ __('pharma::messages.dr') }}
                                        {{ $billing['doctor']['full_name'] ?? '--' }}
                                    </h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-envelope text-dark"></i>
                                            <u class="text-secondary">{{ $billing['doctor']['email'] ?? '--' }}</u>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-phone-call text-dark"></i>
                                            <span>{{ $billing['doctor']['mobile'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">{{ __('messages.patient_detail') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center h-100 gap-3">
                                <div class="image-block">
                                    <img src="{{ $billing['user']['profile_image'] ?? '--' }}"
                                        class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">
                                        {{ $billing['user']['first_name'] . ' ' . $billing['user']['last_name'] ?? '--' }}
                                    </h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        @if ($billing['user']['gender'] !== null)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ph ph-user text-dark"></i>
                                                <span>{{ $billing['user']['gender'] ?? '--' }}</span>
                                            </div>
                                        @endif
                                        @if ($billing['user']['date_of_birth'] !== null)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ph ph-cake text-dark"></i>
                                                <span>{{ date($dateformate, strtotime($billing['user']['date_of_birth'])) ?? '--' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <h5 class="mb-3">{{ __('pharma::messages.pharma_details') }}</h5>
                    <div class="card card-block card-stretch card-height mb-0">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center h-100 gap-3">
                                <div class="image-block">
                                    <img src="{{ $pharma?->profile_image ?? asset('path/to/default/avatar.webp') }}"
                                        class="img-fluid avatar avatar-50 rounded-circle" alt="image">
                                </div>
                                <div class="content-detail">
                                    <h5 class="mb-2">{{ $pharma['first_name'] . ' ' . $pharma['last_name'] ?? '--' }}
                                    </h5>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-envelope text-dark"></i>
                                            <u class="text-secondary">{{ $pharma['email'] ?? '--' }}</u>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ph ph-phone-call text-dark"></i>
                                            <span>{{ $pharma['mobile'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <hr class="my-3" />
            @if (!empty($billing['billingrecord']['billingItem']) && $billing['billingrecord']['billingItem']->isNotEmpty())
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="mb-3">{{ __('messages.service') }}</h5>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered border-top order_table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('messages.sr_no') }}</th>
                                        <th>{{ __('messages.service_name') }}</th>
                                        <th>{{ __('messages.price') }}</th>
                                        <th>{{ __('messages.discount') }}</th>
                                        <th>{{ __('service.inclusive_tax') }}</th>
                                        <th>{{ __('messages.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1 @endphp

                                    @foreach ($billing['billingrecord']['billingItem'] as $item)
                                        <tr>
                                            <td>{{ $index }}</td>
                                            <td>{{ $item->item_name ?? '--' }}</td>
                                            <td class="text-end">
                                                {{ Currency::format($item->service_amount) . ' * ' . $item->quantity ?? '--' }}
                                            </td>
                                            @if ($item->discount_type === 'percentage')
                                                <td class="text-end">{{ $item->discount_value ?? '--' }}%</td>
                                            @else
                                                <td class="text-end">
                                                    {{ Currency::format($item->discount_value) ?? '--' }}</td>
                                            @endif
                                            <td class="text-end">
                                                {{ Currency::format($item->inclusive_tax_amount) ?? '--' }}</td>
                                            <td class="text-end">{{ Currency::format($item->total_amount) ?? '--' }}
                                            </td>
                                        </tr>
                                        @php $index++ @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-0">{{ __('messages.no_record_found') }}</h4>
                    </div>
                </div>
            @endif


            @if (!empty($billing->encounterPrescription))
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="mb-3">{{ __('pharma::messages.medicine') }}</h5>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered border-top order_table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('pharma::messages.medicine_name') }}</th>
                                        <th>{{ __('pharma::messages.form') }}</th>
                                        <th>{{ __('pharma::messages.dosage') }}</th>
                                        <th>{{ __('pharma::messages.frequency') }}</th>
                                        <th>{{ __('pharma::messages.days') }}</th>
                                        <th>{{ __('pharma::messages.expiry_date') }}</th>
                                        <th>{{ __('pharma::messages.price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1 @endphp


                                    @foreach ($billing->encounterPrescription as $item)
                                        <tr>
                                            <td>{{ $item->name ?? '--' }}</td>
                                            <td>{{ $item->medicine->form->name ?? '--' }}</td>
                                            <td>{{ $item->medicine->dosage ?? '--' }}</td>
                                            <td>{{ $item->frequency ?? '--' }}</td>
                                            <td>{{ $item->duration ?? '--' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->medicine->expiry_date)->timezone($timezone)->format($dateformate) ?? '--' }}
                                            </td>
                                            <td>{{ Currency::format($item->total_amount) ?? '--' }}</td>

                                        </tr>
                                        @php $index++ @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mt-5">{{ __('pharma::messages.payment_detail') }}</h6>
                <div id="payment-detail-section">

                </div>
            @else
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-0">{{ __('messages.no_record_found') }}</h4>
                    </div>
                </div>
            @endif


            @php
                $total_amount = 0; // base amount before tax
                $inclusiveTaxTotal = 0;
                $exclusiveTaxTotal = $totalExclusiveTaxAmount ?? 0; // from your code

                foreach ($billing['encounterPrescription'] as $item) {
                    $total_amount += $item->total_amount;
                }

                $exclusiveTax = $exclusiveTaxes;
                $grandTotal = $total_amount + $totalExclusiveTaxAmount;
            @endphp

            <div class="row gy-3 mt-4">
                <div class="col-sm-12">
                    <h5 class="mb-3">{{ __('report.lbl_taxes') }}</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <span>{{ __('pharma::messages.medicine_total') }}</span>
                                <div>{{ Currency::format($total_amount) }}</div>
                            </div>

                            @if (!empty($exclusiveTax))
                                <div class="d-flex justify-content-between py-2 mt-3">
                                    <strong>{{ __('pharma::messages.tax') }}</strong>
                                </div>
                                @foreach ($exclusiveTax as $tax)
                                    @php
                                        $amount =
                                            $tax['type'] === 'percent'
                                                ? \Currency::format(($total_amount * $tax['value']) / 100)
                                                : \Currency::format($tax['value']);
                                    @endphp
                                    <div class="d-flex justify-content-between py-1">
                                        <div>
                                            {{ $tax['title'] ?? __('pharma::messages.exclusive_tax') }}
                                            ({{ $tax['type'] === 'fixed' ? getCurrencySymbol() : '' }}{{ $tax['value'] }}{{ $tax['type'] === 'percent' ? '%' : '' }})
                                        </div>
                                        <div class="fw-semibold">
                                            {{ $tax['type'] === 'fixed' ? '' : '' }}{{ $amount }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            <div class="d-flex justify-content-between pt-3">
                                <strong>{{ __('pharma::messages.grand_total') }}</strong>
                                <strong class="text-secondary">{{ Currency::format($grandTotal ?? 0) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

        </b-col>
    </b-row>
@endsection

@push('after-styles')
    <style>
        .detail-box {
            padding: 0.625rem 0.813rem;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script src="{{ mix('modules/appointment/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script>
        function invoicePrint() {
            window.print()
        }

        function updateStatusAjax(__this, url) {
            console.log(url);
            console.log($billing);
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: {{ $billing['id'] }},
                    status: __this.val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        window.successSnackbar(res.message)
                        setTimeout(() => {
                            location.reload()
                        }, 100);
                    }
                }
            });
        }
        $.ajax({
            url: "{{ route('backend.prescription.payment_detail', ['id' => '__ID__']) }}".replace('__ID__',
                prescriptionId),
            type: 'GET',
            success: function(response) {
                $('#payment-detail-section').html(response);
            }
        });
    </script>
@endpush
