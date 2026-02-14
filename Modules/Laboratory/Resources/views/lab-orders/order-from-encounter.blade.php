@extends('backend.layouts.app')

@section('title', 'Order Lab Tests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Order Lab Tests - Encounter #{{ $encounter_id }}</h4>
                </div>
                <div class="card-body">
                    <form id="lab-order-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                                    <select class="form-select" id="clinic_id" name="clinic_id" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="lab_id" class="form-label">Lab <span class="text-danger">*</span></label>
                                    <select class="form-select" id="lab_id" name="lab_id" required disabled>
                                        <option value="">Select Lab</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="category_id" class="form-label">Test Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="collection_type" class="form-label">Collection Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="collection_type" name="collection_type" required>
                                        <option value="clinic">At Clinic</option>
                                        <option value="home">Home Collection</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="collection_notes" class="form-label">Collection Notes</label>
                            <textarea class="form-control" id="collection_notes" name="collection_notes" rows="2"></textarea>
                        </div>

                        <hr>

                        <h5>Lab Tests</h5>
                        <div id="tests-container">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <select class="form-select" id="test-select" disabled>
                                        <option value="">Select Category and Lab First</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="test-price" placeholder="Price" readonly>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary" id="add-test-btn" disabled>
                                        <i class="fas fa-plus"></i> Add Test
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="selected-tests" class="mb-3"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Total Amount: $<span id="total-amount">0.00</span></h6>
                            </div>
                            <div class="col-md-6">
                                <h6>Final Amount: $<span id="final-amount">0.00</span></h6>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.close()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="fas fa-save"></i> Create Order
                            </button>
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
    let selectedTests = [];
    const encounterId = {{ $encounter_id }};

    // Load labs when clinic is selected
    $('#clinic_id').on('change', function() {
        const clinicId = $(this).val();
        if (clinicId) {
            $.get('/api/lab-orders/get-labs-by-clinic/' + clinicId, function(data) {
                $('#lab_id').html('<option value="">Select Lab</option>');
                data.forEach(function(lab) {
                    $('#lab_id').append('<option value="' + lab.id + '">' + lab.name + '</option>');
                });
                $('#lab_id').prop('disabled', false);
                loadTests();
            });
        } else {
            $('#lab_id').prop('disabled', true).html('<option value="">Select Lab</option>');
            $('#test-select').prop('disabled', true).html('<option value="">Select Category and Lab First</option>');
        }
    });

    // Load tests when category or lab changes
    $('#category_id, #lab_id').on('change', loadTests);

    function loadTests() {
        const categoryId = $('#category_id').val();
        const labId = $('#lab_id').val();
        
        if (categoryId && labId) {
            $.get('/api/lab-orders/get-tests-by-category-and-lab/' + categoryId + '/' + labId, function(data) {
                $('#test-select').html('<option value="">Select Test</option>');
                data.forEach(function(test) {
                    $('#test-select').append('<option value="' + test.id + '" data-price="' + test.price + '">' + test.test_name + ' - $' + test.price + '</option>');
                });
                $('#test-select').prop('disabled', false);
                $('#add-test-btn').prop('disabled', false);
            });
        } else {
            $('#test-select').prop('disabled', true).html('<option value="">Select Category and Lab First</option>');
            $('#add-test-btn').prop('disabled', true);
        }
    }

    // Update price when test is selected
    $('#test-select').on('change', function() {
        const selectedOption = $(this).find(':selected');
        const price = selectedOption.data('price');
        $('#test-price').val(price || 0);
    });

    // Add test to order
    $('#add-test-btn').on('click', function() {
        const testSelect = $('#test-select');
        const testId = testSelect.val();
        const testName = testSelect.find(':selected').text();
        const price = parseFloat($('#test-price').val());

        if (testId && !selectedTests.find(t => t.lab_test_id === testId)) {
            selectedTests.push({
                lab_test_id: testId,
                price: price
            });

            updateTestsDisplay();
            updateTotals();
            testSelect.val('').trigger('change');
            $('#test-price').val('');
        }
    });

    function updateTestsDisplay() {
        const container = $('#selected-tests');
        container.empty();

        selectedTests.forEach(function(test, index) {
            const testName = $('#test-select option[value="' + test.lab_test_id + '"]').text().split(' - $')[0];
            container.append(`
                <div class="row mb-2" data-test-index="${index}">
                    <div class="col-md-6">
                        <input type="hidden" name="tests[${index}][lab_test_id]" value="${test.lab_test_id}">
                        <input type="text" class="form-control" value="${testName}" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="tests[${index}][price]" value="${test.price}" step="0.01" readonly>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-test">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `);
        });
    }

    function updateTotals() {
        const total = selectedTests.reduce((sum, test) => sum + test.price, 0);
        $('#total-amount').text(total.toFixed(2));
        $('#final-amount').text(total.toFixed(2));
        $('#submit-btn').prop('disabled', selectedTests.length === 0);
    }

    // Remove test
    $(document).on('click', '.remove-test', function() {
        const index = $(this).closest('.row').data('test-index');
        selectedTests.splice(index, 1);
        updateTestsDisplay();
        updateTotals();
    });

    // Submit form
    $('#lab-order-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('patient_id', '{{ auth()->id() }}'); // This should be the actual patient ID
        formData.append('doctor_id', '{{ auth()->id() }}'); // This should be the actual doctor ID
        
        // Add tests data
        selectedTests.forEach((test, index) => {
            formData.append(`tests[${index}][lab_test_id]`, test.lab_test_id);
            formData.append(`tests[${index}][price]`, test.price);
        });

        $.ajax({
            url: '/lab-orders/store-from-encounter/' + encounterId,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success('Lab order created successfully');
                if (window.opener) {
                    window.opener.location.reload();
                }
                window.close();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessages = [];
                for (let field in errors) {
                    errorMessages.push(errors[field][0]);
                }
                toastr.error(errorMessages.join('<br>'));
            }
        });
    });
});
</script>
@endpush
