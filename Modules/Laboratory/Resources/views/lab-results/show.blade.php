@extends('backend.layouts.app')

@section('title') {{ __('Lab Result Details') }} @endsection

@section('content')
<x-backend.section-header>
    <div>
        <x-backend.breadcrumbs>
            <x-backend.breadcrumb-item route='{{ route("backend.lab-results.index") }}' icon='ph ph-file-text'>
                {{ __('Lab Results') }}
            </x-backend.breadcrumb-item>
            <x-backend.breadcrumb-item type="active">{{ __('Details') }}</x-backend.breadcrumb-item>
        </x-backend.breadcrumbs>
    </div>
    <x-slot name="toolbar">
        <a href="{{ route('backend.lab-results.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="{{ __('Back') }}">
            <i class="ph ph-arrow-left"></i> {{ __('Back') }}
        </a>
        @can('edit_lab_results')
            <a href="{{ route('backend.lab-results.edit', $labResult->id) }}" class="btn btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                <i class="ph ph-pencil"></i> {{ __('Edit') }}
            </a>
        @endcan
    </x-slot>
</x-backend.section-header>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
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
    </div>
</div>
@endsection
