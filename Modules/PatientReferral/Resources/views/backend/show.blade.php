<!-- Modules/PatientReferral/Resources/views/backend/show.blade.php -->
@extends('backend.layouts.app')

@section('title', 'Referral Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Referral Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('backend.patientreferral.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Referrals
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Patient Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $referral->patient ? $referral->patient->email : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Referral Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Referred By:</strong></td>
                                    <td>{{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Referred To:</strong></td>
                                    <td>{{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Referral Date:</strong></td>
                                    <td>{{ $referral->referral_date->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
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
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Reason for Referral</h5>
                            <p>{{ $referral->reason }}</p>
                        </div>
                    </div>
                    
                    @if($referral->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Additional Notes</h5>
                            <p>{{ $referral->notes }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Actions</h5>
                            <div class="btn-group">
                                @if((auth()->user()->user_type === 'doctor' && $referral->referred_to === auth()->user()->id && $referral->status === 'pending') || 
                                   (in_array(auth()->user()->user_type, ['admin', 'demo_admin']) && $referral->status === 'pending'))
                                    <form action="{{ route('backend.patientreferral.accept', $referral) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to accept this referral and create an appointment?')">
                                            <i class="fas fa-check"></i> Accept Referral & Create Appointment
                                        </button>
                                    </form>
                                @endif
                                
                                @if(auth()->user()->user_type !== 'doctor' || $referral->referred_to !== auth()->user()->id)
                                    <a href="{{ route('backend.patientreferral.edit', $referral) }}" class="btn btn-info">
                                        <i class="fas fa-edit"></i> Edit Referral
                                    </a>
                                @endif
                                
                                @if(auth()->user()->user_type === 'doctor' && $referral->referred_to === auth()->user()->id)
                                    <form action="{{ route('backend.patientreferral.destroy', $referral) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this referral?')">
                                            <i class="fas fa-trash"></i> Delete Referral
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
