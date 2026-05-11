@extends('backend.layouts.app')

@section('title')
    {{ __('medicalcertificate.medical_certificate') }} - {{ $medicalCertificate->certificate_number }}
@endsection

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ __('medicalcertificate.medical_certificate') }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('backend.home') }}">{{ __('sidebar.home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('backend.medical-certificates.index') }}">{{ __('medicalcertificate.medical_certificates') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ $medicalCertificate->certificate_number }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('medicalcertificate.medical_certificate_details') }}</h5>
                    <div class="card-header-right">
                        @can('print_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.print', $medicalCertificate->id) }}" class="btn btn-primary">
                            <i class="fas fa-print"></i> {{ __('messages.print') }}
                        </a>
                        @endcan
                        @can('edit_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.edit', $medicalCertificate->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                        </a>
                        @endcan
                        <a href="{{ route('backend.medical-certificates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('medicalcertificate.certificate_number') }}</th>
                                    <td>{{ $medicalCertificate->certificate_number }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.patient') }}</th>
                                    <td>{{ $medicalCertificate->patient ? $medicalCertificate->patient->full_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.doctor') }}</th>
                                    <td>{{ $medicalCertificate->doctor ? $medicalCertificate->doctor->full_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.type') }}</th>
                                    <td>{{ ucfirst(str_replace('_', ' ', $medicalCertificate->certificate_type)) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.issue_date') }}</th>
                                    <td>{{ $medicalCertificate->issue_date ? $medicalCertificate->issue_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('medicalcertificate.start_date') }}</th>
                                    <td>{{ $medicalCertificate->start_date ? $medicalCertificate->start_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.end_date') }}</th>
                                    <td>{{ $medicalCertificate->end_date ? $medicalCertificate->end_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.duration_days') }}</th>
                                    <td>{{ $medicalCertificate->duration_days }} {{ __('messages.days') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.status') }}</th>
                                    <td>{{ ucfirst($medicalCertificate->status) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('medicalcertificate.is_printed') }}</th>
                                    <td>{{ $medicalCertificate->is_printed ? __('messages.yes') : __('messages.no') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>{{ __('medicalcertificate.diagnosis') }}</h6>
                            <p>{{ $medicalCertificate->diagnosis ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>{{ __('medicalcertificate.reason') }}</h6>
                            <p>{{ $medicalCertificate->reason }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>{{ __('medicalcertificate.recommendations') }}</h6>
                            <p>{{ $medicalCertificate->recommendations ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6>{{ __('messages.notes') }}</h6>
                            <p>{{ $medicalCertificate->notes ?? '-' }}</p>
                        </div>
                    </div>

                    @if($medicalCertificate->clinic)
                    <div class="row">
                        <div class="col-md-12">
                            <h6>{{ __('medicalcertificate.clinic') }}</h6>
                            <p>{{ $medicalCertificate->clinic->clinic_name ?? '-' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
