@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection


@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="table-content mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @if (
                    (auth()->user()->can('edit_doctors') || auth()->user()->can('delete_doctors')) &&
                        !auth()->user()->hasRole('receptionist'))
                    <x-backend.quick-action url='{{ route("backend.$module_name.bulk_action") }}'>
                        <div class="">
                            <select name="action_type" class="form-control select2 col-12" id="quick-action-type"
                                style="width:100%">
                                <option value="">{{ __('messages.no_action') }}</option>
                                <option value="change-status">{{ __('messages.status') }}</option>
                            </select>
                        </div>
                        <div class="select-status d-none quick-action-field" id="change-status-action">
                            <select name="status" class="form-control select2" id="status" style="width:100%">
                                <option value="" selected>{{ __('messages.select_status') }}</option>
                                <option value="1">{{ __('messages.active') }}</option>
                                <option value="0">{{ __('messages.inactive') }}</option>
                            </select>
                        </div>
                    </x-backend.quick-action>
                @endif
                <div>
                    <button type="button" class="btn btn-primary" data-modal="export">
                        <i class="ph ph-export me-1"></i> {{ __('messages.export') }}
                    </button>

                </div>
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter border rounded">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select"
                            style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="0" {{ $filter['status'] == '0' ? 'selected' : '' }}>
                                {{ __('messages.inactive') }}</option>
                            <option value="1" {{ $filter['status'] == '1' ? 'selected' : '' }}>
                                {{ __('messages.active') }}</option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap border rounded">
                    <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..."
                        aria-label="Search" aria-describedby="addon-wrapping">

                </div>

                <button class="btn btn-secondary d-flex align-items-center gap-1 btn-group" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasExample" aria-controls="offcanvasExample"><i
                        class="ph ph-funnel"></i>{{ __('messages.advance_filter') }}</button>

                @if (!auth()->user()->hasRole('receptionist'))
                    <x-buttons.offcanvas href="{{ route('backend.pharma.create') }}"
                        class="d-flex align-items-center gap-1">
                        {{ __('messages.new') }}
                    </x-buttons.offcanvas>
                @endif


            </x-slot>
        </x-backend.section-header>
        <table id="datatable" class="table table-responsive">
        </table>
    </div>

    <x-backend.advance-filter>
        <x-slot name="title">
            <h4>{{ __('service.lbl_advanced_filter') }}</h4>
        </x-slot>

        <div class="form-group datatable-filter">
            <label class="form-label" for="clinic">{{ __('pharma::messages.select_clinic') }}</label>
            <select id="clinic" name="clinic" data-filter="select" class="form-control select2"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'clinic_name']) }}" data-ajax--cache="true"
                width="100%" required>
                <option value="">{{ __('pharma::messages.select_clinic') }}</option>
            </select>
        </div>

        <div class="form-group datatable-filter">
            <label class="form-label" for="contact_number"> {{ __('pharma::messages.contact_number') }}</label>
            <select id="contact_number" name="contact_number" data-filter="select" class="select2 form-control"
                data-ajax--url="{{ route('backend.get_search_data', ['type' => 'pharma_contact_number']) }}"
                data-ajax--cache="true">
                <option value="">{{ __('pharma::messages.contact_number') }}</option>
            </select>
        </div>


        <button type="reset" class="btn btn-danger" id="reset-filter">{{ __('appointment.reset') }}</button>
    </x-backend.advance-filter>

    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="pharmaDetailsOffcanvas"
        aria-labelledby="pharmaDetailsLabel">
        <div class="offcanvas-header mb-5 border-bottom-gray-700">
            <h4 id="pharmaDetailsLabel" class="mb-0">{{ __('pharma::messages.pharma_details') }}</h4>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body pt-0" id="pharmaDetailsContent">
            {{-- Content loaded via AJAX --}}
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end offcanvas-w-20" tabindex="-1" id="changePasswordOffcanvas"
        aria-labelledby="changePasswordLabel">
        <div class="offcanvas-header mb-5 border-bottom-gray-700">
            <h4 class="offcanvas-title" id="changePasswordLabel">{{ __('pharma::messages.change_password') }}</h4>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-close-offcanvas"><i
                    class="ph ph-x-circle"></i></button>
        </div>
        <div class="offcanvas-body pt-0" id="changePasswordContent">
            {{-- Content loaded via AJAX --}}
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush
@push('after-scripts')
    <script src="{{ mix('modules/clinic/script.js') }}"></script>
    <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>
        document.addEventListener('DOMContentLoaded', function() {
            const columns = [
                @if (!auth()->user()->hasRole('receptionist'))
                    {
                        name: 'check',
                        data: 'check',
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                        width: '0%',
                        exportable: false,
                        orderable: false,
                        searchable: false,
                    },
                @endif {
                    data: 'updated_at',
                    name: 'updated_at',
                    title: "{{ __('messages.sl') }}",
                    width: '5%',
                    orderable: true,
                    searchable: false,
                    visible: false,
                },
                {
                    data: 'pharma_name',
                    name: 'pharma_name',
                    title: "{{ __('pharma::messages.pharma') }}",
                },
                {
                    data: 'mobile',
                    name: 'mobile',
                    title: "{{ __('pharma::messages.contact_number') }}",
                },
                {
                    data: 'clinic_name',
                    name: 'clinic_name',
                    title: "{{ __('pharma::messages.clinic') }}",
                },
                {
                    data: 'email_verified_at',
                    name: 'email_verified_at',
                    title: "{{ __('clinic.lbl_verification_status') }}",
                },
                {
                    data: 'status',
                    name: 'status',
                    title: "{{ __('pharma::messages.status') }}",
                }
            ];

            const actionColumn = [{
                data: 'action',
                name: 'action',
                width: '5%',
                orderable: false,
                searchable: false,
                title: "{{ __('clinic.lbl_action') }}"
            }];

            let finalColumns = [...columns, ...actionColumn];

            // Initialize Select2 for quick action dropdowns
            if (window.jQuery && $.fn.select2) {
                $('#quick-action-type').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.no_action') }}"
                });

                $('#status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.select_status') }}"
                });

                $('#column_status').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity,
                    placeholder: "{{ __('messages.all') }}"
                });
            }

            initDatatable({
                url: '{{ route("backend.$module_name.index_data") }}',
                finalColumns,
                orderColumn: [
                    [1, 'desc']
                ],
                advanceFilter: () => {
                    return {
                        pharma: $('#pharma').val(),
                        clinic: $('#clinic').val(),
                        contact_number: $('#contact_number').val(),
                    };
                }
            });

            $('#reset-filter').on('click', function() {
                // Reset Select2 dropdowns
                $('#pharma, #clinic, #contact_number').val(null).trigger('change');
                window.renderedDataTable.ajax.reload(null, false);
            });

            $(document).on('click', '.view-pharma-btn', function() {
                const url = $(this).data('url');
                $('#pharmaDetailsContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#pharmaDetailsOffcanvas').offcanvas('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#pharmaDetailsContent').html(response.html);
                    },
                    error: function() {
                        $('#pharmaDetailsContent').html(
                            '<p class="text-danger">Failed to load supplier details") }}</p>'
                        );
                    }
                });
            });

            $(document).on('click', '.change-pharma-password-btn', function() {
                $('#pharmaDetailsOffcanvas').offcanvas('hide');
                const url = $(this).data('url');
                $('#changePasswordContent').html(
                    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                $('#changePasswordOffcanvas').offcanvas('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#changePasswordContent').html(response.html);
                        bindPasswordValidationEvents
                            (); // ✅ Rebind validation for dynamic content
                    },
                    error: function() {
                        $('#changePasswordContent').html(
                            '<p class="text-danger">Failed to load supplier details.</p>'
                        );
                    }
                });
            });

            // Bind password validation events
            function bindPasswordValidationEvents() {
                const validatePassword = function() {
                    const $password = $('#new-password');
                    const password = $password.val();

                    if (!password) {
                        $password.removeClass('is-invalid is-valid');
                        $('#new-password-error').text('');
                        return false;
                    }

                    if (password.length < 8 || password.length > 14) {
                        $password.removeClass('is-valid').addClass('is-invalid');
                        $('#new-password-error').text('Password must be 8 to 14 characters.');
                        return false;
                    } else {
                        $password.removeClass('is-invalid').addClass('is-valid');
                        $('#new-password-error').text('');
                        return true;
                    }
                };

                const validateConfirmPassword = function() {
                    const password = $('#new-password').val();
                    const confirmPassword = $('#confirm-password').val();

                    if (!confirmPassword) {
                        $('#confirm-password').removeClass('is-invalid is-valid');
                        $('#confirm-password-error').text('');
                    } else if (password !== confirmPassword) {
                        $('#confirm-password').removeClass('is-valid').addClass('is-invalid');
                        $('#confirm-password-error').text('Password and Confirm Password must match.');
                    } else {
                        $('#confirm-password').removeClass('is-invalid').addClass('is-valid');
                        $('#confirm-password-error').text('');
                    }
                };

                // Trigger validation on input and blur
                $(document).on('input blur', '#new-password', function() {
                    validatePassword();
                    validateConfirmPassword(); // Recheck confirm-password on password input
                });

                $(document).on('input blur', '#confirm-password', function() {
                    validateConfirmPassword();
                });

                // Submit handler
                $(document).on('submit', '#change-password-form', function(e) {
                    e.preventDefault();

                    let form = this;
                    let isValid = validatePassword();
                    validateConfirmPassword();
                    if (!isValid || $('#confirm-password').hasClass('is-invalid')) return;

                    let $form = $(this);
                    let $submitBtn = $('#submit-btn');
                    let $loader = $('#btn-loader');
                    let $btnText = $('#btn-text');

                    $submitBtn.prop('disabled', true);
                    $loader.removeClass('d-none');
                    $btnText.text(@json(__('messages.saving')));

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function(response) {
                            $submitBtn.prop('disabled', false);
                            $loader.addClass('d-none');
                            $btnText.text(@json(__('messages.save')));

                            let offcanvasEl = document.querySelector(
                                '#changePasswordOffcanvas');
                            let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                            bsOffcanvas.hide();

                            window.successSnackbar(@json(__('pharma::messages.password_updated_successfully')));
                        },
                        error: function(xhr) {
                            $submitBtn.prop('disabled', false);
                            $loader.addClass('d-none');
                            $btnText.text(@json(__('messages.save')));

                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                if (errors['new-password']) {
                                    $('#new-password').removeClass('is-valid').addClass(
                                        'is-invalid');
                                    $('#new-password-error').text(errors['new-password'][0]);
                                }
                                if (errors['confirm-password']) {
                                    $('#confirm-password').removeClass('is-valid').addClass(
                                        'is-invalid');
                                    $('#confirm-password-error').text(errors['confirm-password']
                                        [0]);
                                }
                            }
                        }
                    });
                });
            }

            function resetQuickAction() {
                const actionValue = $('#quick-action-type').val();
                if (actionValue !== '') {
                    $('#quick-action-apply').removeAttr('disabled');
                    if (actionValue === 'change-status') {
                        $('.quick-action-field').addClass('d-none');
                        $('#change-status-action').removeClass('d-none');
                    } else {
                        $('.quick-action-field').addClass('d-none');
                    }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            }

            $('#quick-action-type').change(function() {
                resetQuickAction();
            });

            $(document).on('update_quick_action', function() {
                // Optional hook
            });

            function dispatchCustomEvent(button) {
                const event = new CustomEvent('custom_form_assign', {
                    detail: {
                        appointment_type: button.getAttribute('data-appointment-type'),
                        appointment_id: button.getAttribute('data-appointment-id'),
                        form_id: button.getAttribute('data-form-id')
                    }
                });

                document.dispatchEvent(event);

                const offcanvasSelector = button.getAttribute('data-assign-target');
                const offcanvasElement = document.querySelector(offcanvasSelector);
                if (offcanvasElement) {
                    const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                    offcanvas.show();
                }
            }
        });
    </script>
@endpush
