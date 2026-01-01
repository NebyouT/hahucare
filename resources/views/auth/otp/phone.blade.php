<x-auth-layout>
    <x-slot name="title">
        @lang('Phone Login')
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
            <h5>{{ __('Login with Phone Number') }}</h5>
            <p class="text-muted">{{ __('Enter your phone number to receive a verification code') }}</p>
        </div>

        <form method="POST" action="{{ route('otp.send') }}">
            @csrf

            <!-- Phone Number -->
            <div class="mb-4">
                <x-label for="phone" :value="__('Phone Number')" />

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-phone"></i> +251
                    </span>
                    <input id="phone" 
                           type="tel" 
                           name="phone" 
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}"
                           placeholder="9XXXXXXXX"
                           required 
                           autofocus
                           pattern="[0-9]{9,10}"
                           maxlength="10"
                           title="{{ __('Enter a valid Ethiopian phone number') }}" />
                </div>
                <small class="text-muted">{{ __('Example: 0912345678 or 912345678') }}</small>
            </div>

            <div class="d-grid gap-2">
                <x-button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>{{ __('Send OTP') }}
                </x-button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted mb-2">{{ __('Or login with') }}</p>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="fas fa-envelope me-2"></i>{{ __('Email & Password') }}
            </a>
        </div>

        <x-slot name="extra">
            @if (Route::has('register'))
                <p class="text-center text-gray-600 mt-4">
                    {{ __('messages.lbl_dont_have_account') }} <a href="{{ route('register') }}"
                        class="underline hover:text-gray-900">{{ __('Register') }}</a>.
                </p>
            @endif
        </x-slot>
    </x-auth-card>

    <script>
        document.getElementById('phone').addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Remove leading 0 if present (we'll add +251 prefix)
            if (this.value.startsWith('0')) {
                this.value = this.value.substring(1);
            }
        });
    </script>
</x-auth-layout>
