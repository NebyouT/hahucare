@if(isset($data->appointmenttransaction))
    @if($data->appointmenttransaction->payment_status == 1)
        <span class="text-capitalize badge bg-success-subtle p-2">{{ getLocalizedPaymentStatus($data->appointmenttransaction->payment_status) }}</span>
    @elseif(optional($data->appointmenttransaction)->payment_status == 0 && optional($data->appointmenttransaction)->advance_payment_status == 1)
        <span class="text-capitalize badge bg-info-subtle p-2">{{ getLocalizedPaymentStatus(5) }}</span>
    @else
        {{-- Payment status change: Admin (Full), Vendor (Own Clinics), Doctor (Own Appointments), Receptionist (Own Clinic), Pharmacist (No), Lab Technician (No) --}}
        @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin') || (auth()->user()->hasRole('vendor') && $data->cliniccenter && $data->cliniccenter->vendor_id == auth()->id()) || (auth()->user()->hasRole('doctor') && $data->doctor_id == auth()->id()) || (auth()->user()->hasRole('receptionist') && $data->cliniccenter && $data->cliniccenter->id == optional(\Modules\Clinic\Models\Receptionist::where('receptionist_id', auth()->id())->first())->clinic_id))
            <select name="branch_for" class="select2 change-select" data-token="{{csrf_token()}}"
                data-url="{{route('backend.appointments.updatePaymentStatus', ['id' => $data->id, 'action_type' => 'update-payment-status'])}}"
                style="width: 100%;" {{ $data->status !== 'checkout' ? 'disabled' : '' }}>
                @php
                    $localizedPaymentStatuses = getLocalizedPaymentStatuses();
                @endphp
                @foreach ($localizedPaymentStatuses as $status)
                    @if($status['value'] != 5) {{-- Exclude advance_paid from dropdown as it's handled separately --}}
                        <option value="{{$status['value']}}" {{optional($data->appointmenttransaction)->payment_status == $status['value'] ? 'selected' : ''}}>
                            {{$status['name']}}
                        </option>
                    @endif
                @endforeach
            </select>
        @else
            {{-- Read-only payment status for roles without permission --}}
            <span class="text-capitalize badge bg-secondary-subtle p-2">{{ getLocalizedPaymentStatus(optional($data->appointmenttransaction)->payment_status) }}</span>
        @endif
    @endif 
@else
<span class="text-capitalize badge bg-danger-subtle p-3">{{ getLocalizedPaymentStatus(2) }}</span>
@endif 
