<!-- Modules/PatientReferral/Resources/views/backend/index.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Patient Referrals')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Patient Referrals</h3>
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link active" id="in-tab" data-bs-toggle="pill" href="#in-referrals" role="tab">In Referral</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="out-tab" data-bs-toggle="pill" href="#out-referrals" role="tab">Out Referral</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- In Referral Tab -->
                        <div class="tab-pane fade show active" id="in-referrals" role="tabpanel">
                            <div class="d-flex justify-content-between mb-3">
                                <h5>Incoming Referrals</h5>
                                <div>
                                    <a href="{{ route('backend.patientreferral.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add Quick Referral
                                    </a>
                                    @if(!auth()->user()->hasRole('receptionist'))
                                    <a href="{{ route('backend.patientreferral.create-advanced') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-plus"></i> Add Advanced Referral
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Referred By</th>
                                        <th>Referred To</th>
                                        <th>Reason</th>
                                        <th>Mode of Referral</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inReferrals as $referral)
                                    <tr>
                                        <td>{{ $referral->id }}</td>
                                        <td>{{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}</td>
                                        <td>{{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}</td>
                                        <td>{{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}</td>
                                        <td>{{ Str::limit($referral->reason, 30) }}</td>
                                        <td>
                                            @if($referral->referral_type === 'advanced')
                                                <span class="badge badge-info">Advanced</span>
                                            @else
                                                <span class="badge badge-secondary">Quick</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($referral->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @case('accepted')
                                                    <span class="badge badge-success">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge badge-danger">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($referral->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $referral->referral_date ? $referral->referral_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</a>

                                                {{-- In Referral: Accept / Reject --}}
                                                @if($referral->status === 'pending')
                                                    @if((auth()->user()->user_type === 'doctor' && $referral->referred_to === auth()->user()->id) || 
                                                       (in_array(auth()->user()->user_type, ['admin', 'demo_admin'])) ||
                                                       (auth()->user()->hasRole('receptionist') && in_array($referral->referred_to, $clinicDoctorIds)))
                                                        <form action="{{ route('backend.patientreferral.accept', $referral) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Accept this referral and create an appointment?')">
                                                                <i class="fas fa-check"></i> Accept
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('backend.patientreferral.reject', $referral) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this referral?')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                @if($referral->referral_type === 'advanced')
                                                    <a href="{{ route('backend.patientreferral.pdf', $referral) }}" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($inReferrals->count() === 0)
                                    <tr>
                                        <td colspan="9" class="text-center">No incoming referrals found</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!-- Out Referral Tab -->
                        <div class="tab-pane fade" id="out-referrals" role="tabpanel">
                            <div class="d-flex justify-content-between mb-3">
                                <h5>Outgoing Referrals</h5>
                                <div>
                                    <a href="{{ route('backend.patientreferral.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add Quick Referral
                                    </a>
                                    @if(!auth()->user()->hasRole('receptionist'))
                                    <a href="{{ route('backend.patientreferral.create-advanced') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-plus"></i> Add Advanced Referral
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Referred By</th>
                                        <th>Referred To</th>
                                        <th>Reason</th>
                                        <th>Mode of Referral</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outReferrals as $referral)
                                    <tr>
                                        <td>{{ $referral->id }}</td>
                                        <td>{{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}</td>
                                        <td>{{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}</td>
                                        <td>{{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}</td>
                                        <td>{{ Str::limit($referral->reason, 30) }}</td>
                                        <td>
                                            @if($referral->referral_type === 'advanced')
                                                <span class="badge badge-info">Advanced</span>
                                            @else
                                                <span class="badge badge-secondary">Quick</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($referral->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @case('accepted')
                                                    <span class="badge badge-success">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge badge-danger">{{ ucfirst($referral->status) }}</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($referral->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $referral->referral_date ? $referral->referral_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</a>

                                                {{-- Out Referral: Change / Re-refer --}}
                                                @if(auth()->user()->user_type !== 'doctor' || $referral->referred_by === auth()->user()->id)
                                                    @if($referral->referral_type === 'advanced')
                                                        <a href="{{ route('backend.patientreferral.edit-advanced', $referral) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Change</a>
                                                    @else
                                                        <a href="{{ route('backend.patientreferral.edit', $referral) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Change</a>
                                                    @endif

                                                    {{-- Re-refer: create new referral from this one --}}
                                                    <a href="{{ route('backend.patientreferral.create') }}?rerfer={{ $referral->id }}" class="btn btn-sm btn-warning"><i class="fas fa-redo"></i> Re-refer</a>
                                                @endif

                                                @if($referral->referral_type === 'advanced')
                                                    <a href="{{ route('backend.patientreferral.pdf', $referral) }}" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($outReferrals->count() === 0)
                                    <tr>
                                        <td colspan="9" class="text-center">No outgoing referrals found</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection