@extends('backend.layouts.app')

@section('title')
    {{ __('medicalcertificate.medical_certificates') }}
@endsection

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h4 class="m-b-10">{{ __('medicalcertificate.medical_certificates') }}</h4>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('backend.home') }}">{{ __('sidebar.home') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ __('medicalcertificate.medical_certificates') }}</li>
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
                    <h5>{{ __('medicalcertificate.medical_certificates') }}</h5>
                    <div class="card-header-right">
                        @can('add_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('messages.add') }}
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-striped table-bordered nowrap" id="medical-certificates-table">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('medicalcertificate.certificate_number') }}</th>
                                    <th>{{ __('medicalcertificate.patient') }}</th>
                                    <th>{{ __('medicalcertificate.doctor') }}</th>
                                    <th>{{ __('medicalcertificate.type') }}</th>
                                    <th>{{ __('medicalcertificate.issue_date') }}</th>
                                    <th>{{ __('medicalcertificate.duration') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#medical-certificates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('backend.medical-certificates.index_data') }}",
                type: "GET"
            },
            columns: [
                { data: 'id' },
                { data: 'certificate_number' },
                { data: 'patient_id' },
                { data: 'doctor_id' },
                { data: 'certificate_type' },
                { data: 'issue_date' },
                { data: 'duration_days' },
                { data: 'status' },
                { data: 'action' },
            ],
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
