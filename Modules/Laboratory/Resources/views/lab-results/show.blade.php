@extends('backend.layouts.app')

@section('title') {{ __('Lab Result Details') }} @endsection

@section('content')

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Lab Result Details') }}</h5>
            <div>
                <a href="{{ route('backend.lab-results.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ph ph-arrow-left"></i> {{ __('Back') }}
                </a>
                @can('edit_lab_results')
                    <a href="{{ route('backend.lab-results.edit', $labResult->id) }}" class="btn btn-primary btn-sm">
                        <i class="ph ph-pencil"></i> {{ __('Edit') }}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
                    <h5 class="card-title mb-4">{{ __('Lab Result Information') }}</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Result Code') }}:</strong>
                            <p>{{ $labResult->result_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Lab Test') }}:</strong>
                            <p>{{ $labResult->labTest->test_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Patient ID') }}:</strong>
                            <p>{{ $labResult->patient_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Doctor ID') }}:</strong>
                            <p>{{ $labResult->doctor_id ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Test Date') }}:</strong>
                            <p>{{ $labResult->test_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Result Date') }}:</strong>
                            <p>{{ $labResult->result_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Sample Type') }}:</strong>
                            <p>{{ $labResult->sample_type ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Sample ID') }}:</strong>
                            <p>{{ $labResult->sample_id ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Result Value') }}:</strong>
                            <p>{{ $labResult->result_value ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Status') }}:</strong>
                            <p>
                                @if($labResult->status == 'pending')
                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                @elseif($labResult->status == 'in_progress')
                                    <span class="badge bg-info">{{ __('In Progress') }}</span>
                                @elseif($labResult->status == 'completed')
                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                @elseif($labResult->status == 'approved')
                                    <span class="badge bg-primary">{{ __('Approved') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>{{ __('Remarks') }}:</strong>
                            <p>{{ $labResult->remarks ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Created At') }}:</strong>
                            <p>{{ $labResult->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Updated At') }}:</strong>
                            <p>{{ $labResult->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
                        </div>
                    </div>
        </div>
    </div>
</div>
@endsection
