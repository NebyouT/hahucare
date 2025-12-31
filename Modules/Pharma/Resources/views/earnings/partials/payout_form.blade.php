<form id="earningForm" class="requires-validation"
    action="{{ isset($pharma) ? route('backend.earning.update', $pharma->id) : route('backend.earning.store') }}"
    method="POST" novalidate>
    @csrf
    @if (isset($pharma))
        @method('PUT')
    @endif

    <div class="offcanvas-body">
        <div class="card mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ $pharma->profile_image ?? asset('default-avatar.png') }}"
                    alt="{ __('pharma::messages.pharma_avatar') }}" class="img-fluid avatar avatar-60 rounded-pill">
                <div class="flex-grow-1">
                    <strong>{{ $pharma->full_name ?? '' }}</strong>
                    <p class="m-0"><small>{{ $pharma->email ?? '' }}</small></p>
                    <p class="m-0"><small>{{ $pharma->mobile ?? '' }}</small></p>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Payment Method --}}
            <div class="col-12 py-2">
                <div class="form-group">
                    <label class="form-label">{{ __('earning.lbl_select_method') }} <span
                            class="text-danger">*</span></label>
                    <select id="payment_method" name="payment_method" data-filter="select" class="form-control select2"
                        data-ajax--url="{{ route('backend.get_search_data', ['type' => 'earning_payment_method']) }}"
                        data-ajax--cache="true" width="100%" required>
                        <option value="">{{ __('earning.lbl_select_method') }}</option>
                    </select>
                    <div class="invalid-feedback">
                        {{ __('pharma::messages.select_methods_required') }}
                    </div>
                    @error('payment_method')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Description --}}
            <div class="col-12">
                <div class="form-group">
                    <label class="form-label">{{ __('earning.lbl_description') }} <span
                            class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" id="description" required>{{ old('description', '') }}</textarea>
                    <div class="invalid-feedback">
                        {{ __('pharma::messages.description_required') }}
                    </div>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Total Pay --}}
            <div class="col-12 py-1">
                <div class="d-flex justify-content-between align-items-center border-top py-3 mt-3">
                    <span class="flex-grow-1">{{ __('earning.total_pay') }}</span>
                    <h6><strong>{{ $totalPay ?? 0 }}</strong></h6>
                </div>
            </div>
        </div>
    </div>

    {{-- Offcanvas footer with buttons --}}
    <div class="offcanvas-footer border-top pt-4">
        <div class="d-grid d-sm-flex justify-content-sm-end gap-3">
            <button type="button" class="btn btn-white fw-600" data-bs-dismiss="offcanvas">
                {{ __('messages.cancel') }}
            </button>
            <button type="submit" class="btn btn-secondary" id="saveBtn">
                <span class="btn-text">{{ __('messages.save') }}</span>
                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"
                    id="saveLoader"></span>
            </button>
        </div>
    </div>
</form>
