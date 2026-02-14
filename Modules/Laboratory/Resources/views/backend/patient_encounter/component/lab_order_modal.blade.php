<div class="modal fade" id="addLabOrder" tabindex="-1" role="dialog" aria-labelledby="labOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labOrderModalLabel">{{ __('laboratory.add_lab_order') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Loader for lab order modal -->
                <div id="lab-order-loader" class="text-center py-5" style="display:none;">
                    <span class="spinner-border text-primary"></span>
                </div>
                <form method="post" id="lab-order-form" class="requires-validation">
                    @csrf
                    <input type="hidden" name="encounter_id" value="{{ $data['id'] }}">
                    <input type="hidden" name="user_id" value="{{ $data['user_id'] }}">
                    <input type="hidden" name="clinic_id" value="{{ $data['clinic_id'] }}">
                    <input type="hidden" name="doctor_id" value="{{ $data['doctor_id'] }}">
                    <input type="hidden" name="patient_id" value="{{ $data['user_id'] }}">
                    <input type="hidden" name="type" value="encounter_lab_order">
                    
                    <!-- Pre-filled Information -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="ph ph-info"></i> {{ __('laboratory.pre_filled_information') }}</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>{{ __('appointment.clinic') }}:</strong> {{ optional($data->clinic)->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('appointment.doctor') }}:</strong> Dr. {{ optional($data->doctor)->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('appointment.patient') }}:</strong> {{ optional($data->user)->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>{{ __('appointment.encounter_id') }}:</strong> #{{ $data['id'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Selection -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lab_id" class="form-label">{{ __('laboratory.lab') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="lab_id" name="lab_id" required>
                                <option value="">{{ __('laboratory.select_lab') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="order_type" class="form-label">{{ __('laboratory.order_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="order_type" name="order_type" required>
                                <option value="outpatient">{{ __('laboratory.outpatient') }}</option>
                                <option value="inpatient">{{ __('laboratory.inpatient') }}</option>
                                <option value="emergency">{{ __('laboratory.emergency') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Priority and Collection Type -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="priority" class="form-label">{{ __('laboratory.priority') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="routine">{{ __('laboratory.routine') }}</option>
                                <option value="urgent">{{ __('laboratory.urgent') }}</option>
                                <option value="stat">{{ __('laboratory.stat') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="collection_type" class="form-label">{{ __('laboratory.collection_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="collection_type" name="collection_type" required>
                                <option value="venipuncture">{{ __('laboratory.venipuncture') }}</option>
                                <option value="urine">{{ __('laboratory.urine') }}</option>
                                <option value="swab">{{ __('laboratory.swab') }}</option>
                                <option value="other">{{ __('laboratory.other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="referred_by" class="form-label">{{ __('laboratory.referred_by') }}</label>
                            <select class="form-select" id="referred_by" name="referred_by">
                                <option value="">{{ __('laboratory.select_referring_doctor') }}</option>
                                <option value="{{ $data['doctor_id'] }}" selected>
                                    Dr. {{ optional($data->doctor)->full_name ?? 'Current Doctor' }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Clinical Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clinical_indication" class="form-label">{{ __('laboratory.clinical_indication') }}</label>
                            <textarea class="form-control" id="clinical_indication" name="clinical_indication" rows="2"
                                placeholder="{{ __('laboratory.reason_for_lab_order') }}"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="diagnosis_suspected" class="form-label">{{ __('laboratory.diagnosis_suspected') }}</label>
                            <textarea class="form-control" id="diagnosis_suspected" name="diagnosis_suspected" rows="2"
                                placeholder="{{ __('laboratory.suspected_diagnosis') }}"></textarea>
                        </div>
                    </div>

                    <!-- Services Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">{{ __('laboratory.lab_services') }} <span class="text-danger">*</span></label>
                            <div id="services-container">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> {{ __('laboratory.select_lab_first_to_see_services') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label">{{ __('laboratory.department') }}</label>
                            <input type="text" class="form-control" id="department" name="department" 
                                placeholder="{{ __('laboratory.department_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="ward_room" class="form-label">{{ __('laboratory.ward_room') }}</label>
                            <input type="text" class="form-control" id="ward_room" name="ward_room" 
                                placeholder="{{ __('laboratory.ward_room_number') }}">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="notes" class="form-label">{{ __('laboratory.notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="{{ __('laboratory.additional_notes') }}"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="collection_notes" class="form-label">{{ __('laboratory.collection_notes') }}</label>
                            <textarea class="form-control" id="collection_notes" name="collection_notes" rows="2"
                                placeholder="{{ __('laboratory.special_instructions_for_collection') }}"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ph ph-x"></i> {{ __('messages.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" onclick="saveLabOrder()">
                    <i class="ph ph-check"></i> {{ __('laboratory.create_lab_order') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
    <script>
        let selectedLabServices = [];

        $(document).ready(function() {
            // Load labs for the current clinic
            loadLabsForClinic({{ $data['clinic_id'] }});

            // Lab selection triggers services loading
            $('#lab_id').on('change', function() {
                const labId = $(this).val();
                loadLabServices(labId);
            });
        });

        function loadLabsForClinic(clinicId) {
            $.get(`/app/lab-orders/get-labs-by-clinic/${clinicId}`, function(data) {
                $('#lab_id').empty().append('<option value="">{{ __('laboratory.select_lab') }}</option>');
                data.forEach(function(lab) {
                    $('#lab_id').append(`<option value="${lab.id}">${lab.name}</option>`);
                });
            }).fail(function(xhr) {
                console.error('Error loading labs:', xhr.responseText);
            });
        }

        function loadLabServices(labId) {
            if (!labId) {
                $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ __('laboratory.select_lab_first_to_see_services') }}</div>');
                selectedLabServices = [];
                return;
            }

            $('#services-container').html('<div class="text-center"><div class="spinner-border text-primary"></div> {{ __('messages.loading') }}...</div>');

            $.get(`/app/lab-orders/get-services-by-lab/${labId}`, function(data) {
                displayLabServices(data);
            }).fail(function(xhr) {
                console.error('Error loading services:', xhr.responseText);
                $('#services-container').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ __('laboratory.error_loading_services') }}</div>');
            });
        }

        function displayLabServices(services) {
            if (services.length === 0) {
                $('#services-container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ __('laboratory.no_services_available') }}</div>');
                return;
            }

            let html = '<div class="row">';
            services.forEach(function(service, index) {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                        id="service_${service.id}" value="${service.id}" 
                                        data-service='${JSON.stringify(service)}'>
                                    <label class="form-check-label" for="service_${service.id}">
                                        <strong>${service.name}</strong>
                                        <span class="badge bg-primary ms-2">${service.price ? '$' + service.price : ''}</span>
                                    </label>
                                </div>
                                ${service.description ? `<p class="text-muted small mb-2">${service.description}</p>` : ''}
                                ${service.category_name ? `<span class="badge bg-light text-dark">${service.category_name}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            $('#services-container').html(html);

            // Handle service checkbox changes
            $('.service-checkbox').on('change', function() {
                const serviceId = $(this).val();
                const isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    selectedLabServices.push(serviceId);
                } else {
                    selectedLabServices = selectedLabServices.filter(id => id !== serviceId);
                }
            });
        }

        function saveLabOrder() {
            if (selectedLabServices.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: '{{ __('laboratory.please_select_at_least_one_service') }}',
                    icon: 'error'
                });
                return;
            }

            const formData = $('#lab-order-form').serialize();
            
            $.ajax({
                url: "/app/lab-orders/store",
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#lab-order-loader').show();
                },
                success: function(response) {
                    $('#lab-order-loader').hide();
                    
                    Swal.fire({
                        title: 'Success',
                        text: '{{ __('laboratory.lab_order_created_successfully') }}',
                        icon: 'success',
                        showClass: {
                            popup: 'animate__animated animate__zoomIn'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__zoomOut'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Close modal
                            $('#addLabOrder').modal('hide');
                            
                            // Reload page to show new lab order
                            location.reload();
                        }
                    });
                },
                error: function(xhr) {
                    $('#lab-order-loader').hide();
                    
                    let errorMessage = '{{ __('laboratory.error_creating_lab_order') }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        }

        // Reset form when modal is hidden
        $('#addLabOrder').on('hidden.bs.modal', function () {
            $('#lab-order-form')[0].reset();
            selectedLabServices = [];
            $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ __('laboratory.select_lab_first_to_see_services') }}</div>');
        });
    </script>
@endpush
