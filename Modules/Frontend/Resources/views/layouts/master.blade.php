<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light"
    dir="{{ session()->has('dir') ? session()->get('dir') : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="base-url" content="{{ env('APP_URL') }}">
    <link rel="icon" type="image/png" href="{{ asset(setting('logo')) }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset(setting('favicon')) }}">

    <title> @yield('title') </title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    <meta name="data_table_limit" content="{{ setting('data_table_limit') }}">
    <meta name="baseUrl" content="{{ url('/') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&family=Kalam:wght@300;400;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('iconly/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">
    <link rel="shortcut icon" href="{{ asset(setting('favicon')) }}">
    <link rel="icon" type="image/ico" href="{{ asset(setting('favicon')) }}" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/frontend/style.css') }}">

    @include('frontend::components.partials.head.plugins')

    @stack('after-styles')

</head>

<body>
    @include('frontend::layouts.header')

    @yield('content')

    @include('frontend::layouts.footer')

    @include('frontend::components.partials.scripts.plugins')

    <div id="back-to-top" style="display: none;" class="animate__animated animate__fadeIn">
            <a class="p-0 btn btn-primary btn-md position-fixed top" id="top" href="#top">
                <i class="ph ph-caret-up align-middle"></i>
            </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ mix('modules/frontend/script.js') }}"></script>
    <script src="{{ mix('js/backend-custom.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        const currencyFormat = (amount) => {
            const DEFAULT_CURRENCY = JSON.parse(@json(json_encode(Currency::getDefaultCurrency(true))))
            const noOfDecimal = DEFAULT_CURRENCY.no_of_decimal
            const decimalSeparator = DEFAULT_CURRENCY.decimal_separator
            const thousandSeparator = DEFAULT_CURRENCY.thousand_separator
            const currencyPosition = DEFAULT_CURRENCY.currency_position
            const currencySymbol = DEFAULT_CURRENCY.currency_symbol
            return formatCurrency(amount, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition,currencySymbol)
        }
        window.currencyFormat = currencyFormat
        window.defaultCurrencySymbol = @json(Currency::defaultSymbol())
    </script>
    @stack('after-scripts')
    
    <!-- Video Meeting Script -->
    <script src="{{ asset('js/video-meeting.js') }}"></script>

    <!-- Floating Call Us Button -->
    <a href="tel:6670" class="floating-call-button">
        <div class="call-button-content">
            <i class="ph ph-phone-call"></i>
            <span>6670</span>
        </div>
    </a>

    <style>
        .floating-call-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50px;
            padding: 12px 24px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            animation: glowing 2s ease-in-out infinite;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .floating-call-button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        @keyframes glowing {
            0% {
                box-shadow: 0 0 5px rgba(102, 126, 234, 0.4),
                            0 0 10px rgba(102, 126, 234, 0.3),
                            0 0 15px rgba(102, 126, 234, 0.2);
            }
            50% {
                box-shadow: 0 0 10px rgba(102, 126, 234, 0.6),
                            0 0 20px rgba(102, 126, 234, 0.5),
                            0 0 30px rgba(102, 126, 234, 0.4);
            }
            100% {
                box-shadow: 0 0 5px rgba(102, 126, 234, 0.4),
                            0 0 10px rgba(102, 126, 234, 0.3),
                            0 0 15px rgba(102, 126, 234, 0.2);
            }
        }

        .call-button-content {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .call-button-content i {
            font-size: 24px;
        }

        @media (max-width: 768px) {
            .floating-call-button {
                bottom: 20px;
                right: 20px;
                padding: 10px 20px;
            }

            .call-button-content {
                font-size: 16px;
            }

            .call-button-content i {
                font-size: 20px;
            }
        }
    </style>
</body>
