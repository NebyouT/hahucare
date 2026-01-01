@extends('frontend::layouts.auth_layout')
@section('title', __('Verify OTP'))

@section('content')
<div class="auth-container" id="otp-verify"
    style="background-image: url('{{ asset('img/frontend/auth-bg.png') }}'); background-position: center center; background-repeat: no-repeat;background-size: cover;">
    <div class="container h-100 min-vh-100">
        <div class="row h-100 min-vh-100 align-items-center">
            <div class="col-xl-4 col-lg-5 col-md-6 my-5">
                <div class="auth-card">
                    <div class="text-center">
                        @include('frontend::components.partials.logo')
                        <div class="auth-card-content mt-3">
                            <h5 class="mb-2">{{ __('Verify Your Phone') }}</h5>
                            <p class="text-muted mb-1">{{ __('We sent a 6-digit code to') }}</p>
                            <p class="fw-bold mb-4">+{{ $phone }}</p>
                            
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

                            <form method="POST" action="{{ route('otp.verify') }}" class="requires-validation" novalidate>
                                @csrf

                                <div class="input-group custom-input-group mb-3">
                                    <input type="text" 
                                           name="otp" 
                                           class="form-control text-center" 
                                           placeholder="000000"
                                           required 
                                           autofocus
                                           autocomplete="one-time-code"
                                           inputmode="numeric"
                                           pattern="[0-9]{6}"
                                           maxlength="6"
                                           id="otp-input"
                                           style="font-size: 24px; letter-spacing: 8px; padding: 15px;">
                                </div>
                                <small class="text-muted d-block mb-4">{{ __('Code expires in 5 minutes') }}</small>

                                <div class="d-flex justify-content-center gap-3 mt-4 auth-btn">
                                    <button type="submit" class="btn btn-secondary sign-in-btn w-100">
                                        <i class="ph ph-check-circle me-2"></i>{{ __('Verify & Login') }}
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex justify-content-center flex-wrap gap-1 mt-4 mb-3">
                                <span class="font-size-14 text-body">{{ __("Didn't receive the code?") }}</span>
                            </div>
                            
                            <form method="POST" action="{{ route('otp.resend') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-secondary fw-bold p-0">
                                    {{ __('Resend OTP') }}
                                </button>
                            </form>

                            <div class="d-flex justify-content-center flex-wrap gap-1 mt-4">
                                <a href="{{ route('otp.phone.form') }}" class="text-muted font-size-14">
                                    <i class="ph ph-arrow-left me-1"></i>{{ __('Change phone number') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('otp-input').addEventListener('input', function(e) {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endsection
