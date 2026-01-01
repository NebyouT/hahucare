@extends('frontend::layouts.auth_layout')
@section('title', __('Complete Registration'))

@section('content')
<div class="auth-container" id="otp-register"
    style="background-image: url('{{ asset('img/frontend/auth-bg.png') }}'); background-position: center center; background-repeat: no-repeat;background-size: cover;">
    <div class="container h-100 min-vh-100">
        <div class="row h-100 min-vh-100 align-items-center">
            <div class="col-xl-4 col-lg-5 col-md-6 my-5">
                <div class="auth-card">
                    <div class="text-center">
                        @include('frontend::components.partials.logo')
                        <div class="auth-card-content mt-3">
                            <h5 class="mb-2">{{ __('Complete Your Profile') }}</h5>
                            <p class="text-muted mb-1">{{ __('Phone verified:') }} <strong>+{{ $phone }}</strong></p>
                            <p class="text-muted mb-4">{{ __('Please provide your details to complete registration') }}</p>
                            
                            @if(session('success'))
                                <div class="alert alert-success mb-3">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger mb-3">
                                    @foreach ($errors->all() as $error)
                                        <p class="mb-0">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('otp.register') }}" class="requires-validation text-start" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" 
                                               name="first_name" 
                                               id="first_name"
                                               class="form-control" 
                                               placeholder="{{ __('Enter your first name') }}"
                                               value="{{ old('first_name') }}"
                                               required>
                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                    <div class="input-group custom-input-group">
                                        <input type="text" 
                                               name="last_name" 
                                               id="last_name"
                                               class="form-control" 
                                               placeholder="{{ __('Enter your last name') }}"
                                               value="{{ old('last_name') }}"
                                               required>
                                        <span class="input-group-text"><i class="ph ph-user"></i></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <div class="input-group custom-input-group">
                                        <input type="email" 
                                               name="email" 
                                               id="email"
                                               class="form-control" 
                                               placeholder="{{ __('Enter your email (optional)') }}"
                                               value="{{ old('email') }}">
                                        <span class="input-group-text"><i class="ph ph-envelope-simple"></i></span>
                                    </div>
                                    <small class="text-muted">{{ __('If not provided, we will create one based on your phone number') }}</small>
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">{{ __('Gender') }}</label>
                                    <select name="gender" id="gender" class="form-control form-select">
                                        <option value="">{{ __('Select Gender') }}</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <div class="input-group custom-input-group">
                                        <input type="date" 
                                               name="date_of_birth" 
                                               id="date_of_birth"
                                               class="form-control" 
                                               value="{{ old('date_of_birth') }}"
                                               max="{{ date('Y-m-d') }}">
                                        <span class="input-group-text"><i class="ph ph-calendar"></i></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-4 auth-btn">
                                    <button type="submit" class="btn btn-secondary sign-in-btn w-100">
                                        <i class="ph ph-user-plus me-2"></i>{{ __('Complete Registration') }}
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex justify-content-center flex-wrap gap-1 mt-4">
                                <a href="{{ route('otp.phone.form') }}" class="text-muted font-size-14">
                                    <i class="ph ph-arrow-left me-1"></i>{{ __('Start over') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
