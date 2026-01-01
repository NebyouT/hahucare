<x-auth-layout>
    <x-slot name="title">
        @lang('Complete Registration')
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
            <h5>{{ __('Complete Your Profile') }}</h5>
            <p class="text-muted">
                {{ __('Phone verified:') }} <strong>+{{ $phone }}</strong><br>
                {{ __('Please provide your details to complete registration') }}
            </p>
        </div>

        <form method="POST" action="{{ route('otp.register') }}">
            @csrf

            <!-- First Name -->
            <div class="mb-3">
                <x-label for="first_name" :value="__('First Name')" />
                <x-input id="first_name" 
                         type="text" 
                         name="first_name" 
                         :value="old('first_name')"
                         placeholder="{{ __('Enter your first name') }}"
                         required 
                         autofocus />
            </div>

            <!-- Last Name -->
            <div class="mb-3">
                <x-label for="last_name" :value="__('Last Name')" />
                <x-input id="last_name" 
                         type="text" 
                         name="last_name" 
                         :value="old('last_name')"
                         placeholder="{{ __('Enter your last name') }}"
                         required />
            </div>

            <!-- Email (Optional) -->
            <div class="mb-3">
                <x-label for="email">
                    {{ __('Email') }} <span class="text-muted">({{ __('Optional') }})</span>
                </x-label>
                <x-input id="email" 
                         type="email" 
                         name="email" 
                         :value="old('email')"
                         placeholder="{{ __('Enter your email (optional)') }}" />
                <small class="text-muted">
                    {{ __('If not provided, we will create one for you based on your phone number') }}
                </small>
            </div>

            <!-- Gender -->
            <div class="mb-3">
                <x-label for="gender" :value="__('Gender')" />
                <select id="gender" 
                        name="gender" 
                        class="form-control @error('gender') is-invalid @enderror">
                    <option value="">{{ __('Select Gender') }}</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
            </div>

            <!-- Date of Birth -->
            <div class="mb-4">
                <x-label for="date_of_birth">
                    {{ __('Date of Birth') }} <span class="text-muted">({{ __('Optional') }})</span>
                </x-label>
                <x-input id="date_of_birth" 
                         type="date" 
                         name="date_of_birth" 
                         :value="old('date_of_birth')"
                         max="{{ date('Y-m-d') }}" />
            </div>

            <div class="d-grid gap-2">
                <x-button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>{{ __('Complete Registration') }}
                </x-button>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('otp.phone.form') }}" class="text-muted">
                <i class="fas fa-arrow-left me-1"></i>{{ __('Start over') }}
            </a>
        </div>

        <x-slot name="extra">
        </x-slot>
    </x-auth-card>
</x-auth-layout>
