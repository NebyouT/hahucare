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
                        <a href="{{ route('backend.patientreferral.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Referral
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Referred By</th>
                                <th>Referred To</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                            <tr>
                                <td>{{ $referral->id }}</td>
                                <td>{{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}</td>
                                <td>{{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}</td>
                                <td>{{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}</td>
                                <td>{{ Str::limit($referral->reason, 30) }}</td>
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
                                <td>{{ $referral->referral_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('backend.patientreferral.show', $referral) }}" class="btn btn-sm btn-primary">View</a>
                                    
                                    @if((auth()->user()->user_type === 'doctor' && $referral->referred_to === auth()->user()->id && $referral->status === 'pending') || 
                                   (in_array(auth()->user()->user_type, ['admin', 'demo_admin']) && $referral->status === 'pending'))
                                        <form action="{{ route('backend.patientreferral.accept', $referral) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to accept this referral and create an appointment?')">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(auth()->user()->user_type !== 'doctor' || $referral->referred_to !== auth()->user()->id)
                                        <a href="{{ route('backend.patientreferral.edit', $referral) }}" class="btn btn-sm btn-info">Edit</a>
                                    @endif
                                    
                                    @if(auth()->user()->user_type === 'doctor' && $referral->referred_to === auth()->user()->id)
                                        <form action="{{ route('backend.patientreferral.destroy', $referral) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this referral?')">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection