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
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                            <tr>
                                <td>{{ $referral->id }}</td>
                                <td>{{ $referral->patient_id }}</td>
                                <td>{{ $referral->referred_by }}</td>
                                <td>{{ $referral->referred_to }}</td>
                                <td>{{ $referral->status }}</td>
                                <td>{{ $referral->referral_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('backend.patientreferral.edit', $referral) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ route('backend.patientreferral.destroy', $referral) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
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