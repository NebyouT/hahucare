<x-auth-layout>
    <x-slot name="title">
        @lang('Verify OTP')
    </x-slot>

    <x-auth-card>
        <x-slot name="logo">
            <x-application-logo />
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        @if(session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <div class="text-center mb-4">
            <h5>{{ __('Verify Your Phone') }}</h5>
            <p class="text-muted">
                {{ __('We sent a 6-digit code to') }}<br>
                <strong>+{{ $phone }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf

            <!-- OTP Input -->
            <div class="mb-4">
                <x-label for="otp" :value="__('Enter OTP Code')" />

                <input id="otp" 
                       type="text" 
                       name="otp" 
                       class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                       placeholder="000000"
                       required 
                       autofocus
                       autocomplete="one-time-code"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       style="font-size: 24px; letter-spacing: 8px;"
                       title="{{ __('Enter the 6-digit code') }}" />
                
                <small class="text-muted d-block text-center mt-2">
                    {{ __('Code expires in 5 minutes') }}
                </small>
            </div>

            <div class="d-grid gap-2">
                <x-button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check me-2"></i>{{ __('Verify & Login') }}
                </x-button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted mb-2">{{ __("Didn't receive the code?") }}</p>
            <form method="POST" action="{{ route('otp.resend') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link p-0">
                    {{ __('Resend OTP') }}
                </button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('otp.phone.form') }}" class="text-muted">
                <i class="fas fa-arrow-left me-1"></i>{{ __('Change phone number') }}
            </a>
        </div>

        <x-slot name="extra">
        </x-slot>
    </x-auth-card>

    <script>
        document.getElementById('otp').addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Auto-submit when 6 digits entered
        document.getElementById('otp').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Optional: auto-submit
                // this.form.submit();
            }
        });
    </script>
</x-auth-layout>
