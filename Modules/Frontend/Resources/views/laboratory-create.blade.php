@extends('frontend::layouts.master')

@section('title', 'Create Lab Test Request')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Lab Test Request</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('laboratory') }}">Laboratory</a></li>
                        <li class="breadcrumb-item active">Create Request</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ph ph-plus-circle me-2"></i>
                        Lab Test Request Form
                    </h5>
                </div>
                <div class="card-body">
                    <form id="lab-request-form" method="POST" action="{{ route('laboratory.request.store') }}">
                        @csrf
                        
                        <!-- Patient Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ph ph-user me-2"></i>Patient Information
                                </h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="patient_phone" name="patient_phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="patient_email" name="patient_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_age" class="form-label">Age *</label>
                                <input type="number" class="form-control" id="patient_age" name="patient_age" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_gender" class="form-label">Gender *</label>
                                <select class="form-control" id="patient_gender" name="patient_gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferred_date" class="form-label">Preferred Date *</label>
                                <input type="date" class="form-control" id="preferred_date" name="preferred_date" required>
                            </div>
                        </div>

                        <!-- Test Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ph ph-test-tube me-2"></i>Test Selection
                                </h6>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="category_id" class="form-label">Test Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Select Tests *</label>
                                <div id="tests-container" class="row">
                                    <div class="col-12">
                                        <p class="text-muted">Please select a category to view available tests</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ph ph-note-text me-2"></i>Additional Information
                                </h6>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="symptoms" class="form-label">Symptoms / Reason for Test</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="3" placeholder="Please describe your symptoms or reason for needing these tests..."></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="doctor_referral" class="form-label">Doctor Referral (if any)</label>
                                <textarea class="form-control" id="doctor_referral" name="doctor_referral" rows="2" placeholder="Doctor name and referral details..."></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any additional information..."></textarea>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="ph ph-receipt me-2"></i>Request Summary
                                </h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Selected Tests:</strong> <span id="selected-tests-count">0</span></p>
                                                <p class="mb-1"><strong>Total Amount:</strong> $<span id="total-amount">0.00</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Preferred Date:</strong> <span id="summary-date">Not selected</span></p>
                                                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('laboratory') }}" class="btn btn-secondary">
                                        <i class="ph ph-arrow-left me-2"></i>Back to Laboratory
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submit-btn">
                                        <i class="ph ph-paper-plane me-2"></i>Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#preferred_date').attr('min', today);

    // Load tests when category changes
    $('#category_id').on('change', function() {
        var categoryId = $(this).val();
        loadTests(categoryId);
    });

    // Update summary when date changes
    $('#preferred_date').on('change', function() {
        var date = $(this).val();
        if (date) {
            var formattedDate = new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            $('#summary-date').text(formattedDate);
        } else {
            $('#summary-date').text('Not selected');
        }
    });

    function loadTests(categoryId) {
        $.ajax({
            url: '/api/lab-tests/by-category/' + categoryId,
            method: 'GET',
            success: function(response) {
                displayTests(response.tests);
            },
            error: function() {
                $('#tests-container').html('<div class="col-12"><p class="text-muted">No tests found for this category</p></div>');
            }
        });
    }

    function displayTests(tests) {
        var html = '';
        if (tests.length > 0) {
            tests.forEach(function(test) {
                var price = parseFloat(test.final_price || test.price || 0);
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input test-checkbox" type="checkbox" 
                                           value="${test.id}" data-name="${test.test_name}" data-price="${price}" 
                                           id="test_${test.id}" name="tests[]">
                                    <label class="form-check-label" for="test_${test.id}">
                                        <strong>${test.test_name}</strong><br>
                                        <small class="text-muted">${test.description || ''}</small><br>
                                        <span class="text-primary">$${price.toFixed(2)}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html = '<div class="col-12"><p class="text-muted">No tests found for this category</p></div>';
        }
        $('#tests-container').html(html);
        
        // Add event listeners to checkboxes
        $('.test-checkbox').on('change', updateSummary);
    }

    function updateSummary() {
        var selectedTests = $('.test-checkbox:checked');
        var count = selectedTests.length;
        var total = 0;

        selectedTests.each(function() {
            total += parseFloat($(this).data('price'));
        });

        $('#selected-tests-count').text(count);
        $('#total-amount').text(total.toFixed(2));
    }

    // Form submission
    $('#lab-request-form').on('submit', function(e) {
        e.preventDefault();
        
        var selectedTests = $('.test-checkbox:checked');
        if (selectedTests.length === 0) {
            alert('Please select at least one test');
            return;
        }

        $('#submit-btn').prop('disabled', true).html('<i class="ph ph-spinner me-2"></i>Submitting...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Lab test request submitted successfully!');
                window.location.href = '{{ route("laboratory") }}';
            },
            error: function(xhr) {
                alert('Error submitting request. Please try again.');
                $('#submit-btn').prop('disabled', false).html('<i class="ph ph-paper-plane me-2"></i>Submit Request');
            }
        });
    });
});
</script>
@endpush
