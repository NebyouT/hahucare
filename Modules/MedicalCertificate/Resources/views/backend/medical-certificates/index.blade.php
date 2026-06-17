@extends('backend.layouts.app')

@section('title', __('medicalcertificate.medical_certificates'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('medicalcertificate.medical_certificates') }}</h4>
                    @can('add_medical_certificate')
                    <a href="{{ route('backend.medical-certificates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('messages.add') }}
                    </a>
                    @endcan
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

@push('after-scripts')
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
