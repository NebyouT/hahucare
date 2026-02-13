<form action="{{ route('backend.pharma.update-password', $pharmaDetail->id ?? 0) }}" 
    method="POST" 
    id="change-password-form" 
    class="requires-validation" 
    novalidate>
  @csrf

  <!-- Password -->
  <div class="form-group">
      <label for="password" class="form-label">{{ __('users.lbl_new_password') }} <span class="text-danger">*</span></label>
      <div class="input-group">
        <input type="password" 
              class="form-control" 
              id="new-password" 
              name="new-password" 
              placeholder="{{ __('pharma::messages.password') }}" 
              required
              autocomplete="new-password">
               <span class="input-group-text">
                <i class="ph ph-eye" id="password-eye"></i>
            </span>
        </div>
      <div class="invalid-feedback"  id="new-password-error">{{ __('pharma::messages.password_required') }}</div>
  </div>

  <!-- Confirm Password -->
  <div class="form-group">
      <label for="confirm_password" class="form-label">{{ __('pharma::messages.confirm_password') }} <span class="text-danger">*</span></label>
      <div class="input-group">
        <input type="password" 
              class="form-control" 
              id="confirm-password" 
              name="confirm-password" 
              placeholder="{{ __('pharma::messages.confirm_password') }}" 
              required
              autocomplete="new-password">
               <span class="input-group-text">
                <i class="ph ph-eye" id="password-eye"></i>
            </span>
      </div>
      <div class="invalid-feedback" id="confirm-password-error">{{ __('pharma::messages.confirm_password_required') }}</div>
  </div>

  <!-- Action Buttons -->
  <div class="offcanvas-footer border-top-gray-700 mt-5 pt-5">
     <div class="d-flex align-items-center justify-content-end gap-3">
       <button type="button" class="btn btn-white" data-bs-dismiss="offcanvas">
         {{ __('pharma::messages.close') }}
       </button>
       <button type="submit" class="btn btn-secondary" id="submit-btn">
         <span id="btn-text">{{ __('pharma::messages.save') }}</span>
       </button>
     </div>
  </div>
  
</form>
