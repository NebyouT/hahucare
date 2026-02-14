<div class="modal fade" id="addLabOrder" tabindex="-1" role="dialog" aria-labelledby="labOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
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
                    <input type="hidden" name="type" value="encounter_lab_order">
                    <input type="hidden" name="clinic_id" value="{{ $data->clinic_id ?? '' }}">
                    <input type="hidden" name="patient_id" value="{{ $data->user_id ?? '' }}">
                    <input type="hidden" name="doctor_id" value="{{ $data->doctor_id ?? '' }}">
                    <input type="hidden" name="encounter_id" value="{{ $data->id ?? '' }}">
                    <input type="hidden" name="order_type" value="outpatient">
                    <input type="hidden" name="priority" value="routine">
                    <input type="hidden" name="collection_type" value="venipuncture">
                    
                    <!-- Pre-filled Information -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="ph ph-info"></i> Encounter Information</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Clinic:</strong> {{ optional($data->clinic)->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Doctor:</strong> {{ optional($data->doctor)->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Patient:</strong> {{ optional($data->user)->full_name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Encounter ID:</strong> #{{ $data->id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="lab_id" class="form-label">Select Lab <span class="text-danger">*</span></label>
                            <select class="form-select" id="lab_id" name="lab_id" required>
                                <option value="">Choose Lab</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="category_id" class="form-label">Select Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required disabled>
                                <option value="">Choose Category</option>
                            </select>
                        </div>
                    </div>

                    <!-- Lab Services Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Services <span class="text-danger">*</span></label>
                            <div id="services-container">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Please select lab and category first to see services
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral and Notes -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="referral_notes" class="form-label">Referral & Clinical Notes</label>
                            <textarea class="form-control" id="referral_notes" name="referral_notes" rows="4"
                                placeholder="Add referral information or clinical notes..."></textarea>
                            <small class="text-muted">Include any relevant clinical information, symptoms, or specific requirements for the laboratory</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ph ph-x"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveLabOrder()">
                    <i class="ph ph-check"></i> Create Lab Order
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

            // Lab selection triggers category loading
            $('#lab_id').on('change', function() {
                const labId = $(this).val();
                loadCategoriesForLab(labId);
                resetServices();
            });

            // Category selection triggers services loading
            $('#category_id').on('change', function() {
                const categoryId = $(this).val();
                loadLabServices(categoryId);
            });
        });

        function loadLabsForClinic(clinicId) {
            console.log('Loading labs for clinic:', clinicId); // Debug log
            $.get(`/app/lab-orders/get-labs-by-clinic/${clinicId}`, function(data) {
                console.log('Labs received:', data); // Debug log
                $('#lab_id').empty().append('<option value="">Choose Lab</option>');
                data.forEach(function(lab) {
                    $('#lab_id').append(`<option value="${lab.id}">${lab.name}</option>`);
                });
            }).fail(function(xhr) {
                console.error('Error loading labs:', xhr.responseText);
            });
        }

        function loadCategoriesForLab(labId) {
            if (!labId) {
                $('#category_id').empty().append('<option value="">Choose Category</option>').prop('disabled', true);
                return;
            }

            console.log('Loading categories for lab:', labId); // Debug log
            $('#category_id').empty().append('<option value="">Loading categories...</option>').prop('disabled', false);

            $.get(`/app/lab-orders/get-categories-by-lab/${labId}`, function(data) {
                console.log('Categories received:', data); // Debug log
                
                $('#category_id').empty().append('<option value="">Choose Category</option>');
                
                if (data.error) {
                    console.error('Server error:', data.error);
                    $('#category_id').empty().append('<option value="">Error loading categories</option>').prop('disabled', true);
                    return;
                }
                
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(function(category) {
                        $('#category_id').append(`<option value="${category.id}">${category.name}</option>`);
                    });
                } else {
                    $('#category_id').empty().append('<option value="">No categories available</option>').prop('disabled', true);
                }
            }).fail(function(xhr) {
                console.error('Error loading categories:', xhr.responseText);
                $('#category_id').empty().append('<option value="">Error loading categories</option>').prop('disabled', true);
            });
        }

        function loadLabServices(categoryId) {
            if (!categoryId) {
                $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select lab and category first to see services</div>');
                selectedLabServices = [];
                return;
            }

            console.log('Loading services for category:', categoryId); // Debug log
            $('#services-container').html('<div class="text-center"><div class="spinner-border text-primary"></div> Loading...</div>');

            $.get(`/app/lab-orders/get-services-by-category/${categoryId}`, function(data) {
                console.log('Services received:', data); // Debug log
                displayLabServices(data);
            }).fail(function(xhr) {
                console.error('Error loading services:', xhr.responseText);
                $('#services-container').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error loading services</div>');
            });
        }

        function displayLabServices(services) {
            if (services.length === 0) {
                $('#services-container').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No services available</div>');
                return;
            }

            let html = '<div class="row">';
            services.forEach(function(service) {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input service-checkbox" type="checkbox" 
                                        id="service_${service.id}" value="${service.id}">
                                    <label class="form-check-label" for="service_${service.id}">
                                        <strong>${service.name}</strong>
                                        ${service.price ? '<span class="badge bg-primary ms-2">$' + service.price + '</span>' : ''}
                                    </label>
                                </div>
                                ${service.description ? `<p class="text-muted small mb-2">${service.description}</p>` : ''}
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

        function resetServices() {
            $('#category_id').empty().append('<option value="">Choose Category</option>').prop('disabled', true);
            $('#services-container').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select lab and category first to see services</div>');
            selectedLabServices = [];
        }

        function saveLabOrder() {
            if (selectedLabServices.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please select at least one service',
                    icon: 'error'
                });
                return;
            }

            // Add selected services to form data
            const formData = $('#lab-order-form').serialize();
            const servicesData = selectedLabServices.map(id => `services[]=${id}`).join('&');
            const fullFormData = formData + '&' + servicesData;
            
            $.ajax({
                url: "/app/lab-orders/store",
                type: 'POST',
                data: fullFormData,
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
            resetServices();
        });
    </script>
@endpush
