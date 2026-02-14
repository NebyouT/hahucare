@extends('frontend::layouts.auth_layout')
@section('title', __('Phone Login'))

@section('content')
<div class="auth-container" id="otp-phone"
    style="background-image: url('{{ asset('img/frontend/auth-bg.png') }}'); background-position: center center; background-repeat: no-repeat;background-size: cover;">
    <div class="container h-100 min-vh-100">
        <div class="row h-100 min-vh-100 align-items-center">
            <div class="col-xl-4 col-lg-5 col-md-6 my-5">
                <div class="auth-card">
                    <div class="text-center">
                        @include('frontend::components.partials.logo')
                        <div class="auth-card-content mt-3">
                            <h5 class="mb-2">{{ __('Login with Phone Number') }}</h5>
                            <p class="text-muted mb-4">{{ __('Enter your phone number to receive a verification code') }}</p>
                            
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

                            <form method="POST" action="{{ route('otp.send') }}" class="requires-validation" novalidate>
                                @csrf

                                <div class="input-group custom-input-group mb-3" style="flex-direction: column; align-items: flex-start;">
                                    <div style="width: 100%; display: flex;">
                                        <span class="input-group-text" style="border-radius: 8px 0 0 8px;">+251</span>
                                        <input type="tel" 
                                               name="phone" 
                                               class="form-control" 
                                               placeholder="9XXXXXXXX"
                                               value="{{ old('phone') }}"
                                               required 
                                               autofocus
                                               pattern="[0-9]{9,10}"
                                               maxlength="10"
                                               id="phone-input">
                                        <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                    </div>
                                    <small class="text-muted mt-1">{{ __('Example: 0912345678 or 912345678') }}</small>
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-4 auth-btn">
                                    <button type="submit" class="btn btn-secondary sign-in-btn w-100">
                                        <i class="ph ph-paper-plane-tilt me-2"></i>{{ __('Send OTP') }}
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex justify-content-center flex-wrap gap-1 mt-4 mb-3">
                                <span class="font-size-14 text-body">{{ __('Or login with') }}</span>
                            </div>
                            
                            <a href="{{ route('login-page') }}" class="btn btn-outline-secondary w-100" style="border-radius: 8px;">
                                <i class="ph ph-envelope-simple me-2"></i>{{ __('Email & Password') }}
                            </a>

                            <div class="d-flex justify-content-center flex-wrap gap-1 mt-4 mb-3">
                                <span class="font-size-14 text-body">{{ __("messages.not_a_member") }}</span>
                                <a href="{{ route('register-page') }}"
                                    class="text-secondary font-size-14 fw-bold">{{ __("messages.register_now") }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('phone-input').addEventListener('input', function(e) {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Remove leading 0 if present
        if (this.value.startsWith('0')) {
            this.value = this.value.substring(1);
        }
    });
</script>
@endsection
