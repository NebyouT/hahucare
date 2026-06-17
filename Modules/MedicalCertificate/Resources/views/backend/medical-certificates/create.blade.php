@extends('backend.layouts.app')

@section('title', __('medicalcertificate.create_medical_certificate'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('medicalcertificate.create_medical_certificate') }}</h4>
                    <a href="{{ route('backend.medical-certificates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
                <div class="card-body">
                    <form id="medical-certificate-form" method="POST" action="{{ route('backend.medical-certificates.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id">{{ __('medicalcertificate.patient') }} <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="patient_id" name="patient_id" required>
                                        <option value="">{{ __('messages.select') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="encounter_id">{{ __('medicalcertificate.encounter') }}</label>
                                    <select class="form-control select2" id="encounter_id" name="encounter_id">
                                        <option value="">{{ __('messages.select') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="certificate_type">{{ __('medicalcertificate.certificate_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="certificate_type" name="certificate_type" required>
                                        <option value="medical_leave">{{ __('medicalcertificate.medical_leave') }}</option>
                                        <option value="fitness">{{ __('medicalcertificate.fitness') }}</option>
                                        <option value="recovery">{{ __('medicalcertificate.recovery') }}</option>
                                        <option value="other">{{ __('messages.other') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="issue_date">{{ __('medicalcertificate.issue_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">{{ __('medicalcertificate.start_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">{{ __('medicalcertificate.end_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="diagnosis">{{ __('medicalcertificate.diagnosis') }}</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="reason">{{ __('medicalcertificate.reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="recommendations">{{ __('medicalcertificate.recommendations') }}</label>
                            <textarea class="form-control" id="recommendations" name="recommendations" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('messages.notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                            <a href="{{ route('backend.medical-certificates.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('#patient_id').select2({
            ajax: {
                url: "{{ url('app/customers/index_list') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            },
            minimumInputLength: 2
        });

        $('#medical-certificate-form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        window.location.href = "{{ route('backend.medical-certificates.index') }}";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                }
            });
        });
    });
</script>
@endpush
