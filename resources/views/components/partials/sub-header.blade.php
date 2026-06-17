<div class="iq-navbar-header navs-bg-color pr-hide">
    <div class="container-fluid iq-container pb-0">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb gap-2 heading-font m-0">
                            @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))
                                <li class="breadcrumb-item"><a
                                        href="{{ route('backend.home') }}">{{ __('sidebar.home') }}</a></li>
                            @elseif(auth()->user()->hasRole('doctor'))
                                <li class="breadcrumb-item"><a
                                        href="{{ route('backend.doctor-dashboard') }}">{{ __('sidebar.home') }}</a>
                                </li>
                            @elseif(auth()->user()->hasRole('receptionist'))
                                <li class="breadcrumb-item"><a
                                        href="{{ route('backend.receptionist-dashboard') }}">{{ __('sidebar.home') }}</a>
                                </li>
                            @elseif(auth()->user()->hasRole('vendor'))
                                <li class="breadcrumb-item"><a
                                        href="{{ route('backend.vendor-dashboard') }}">{{ __('sidebar.home') }}</a>
                                </li>
                            @elseif (auth()->user()->hasRole('pharma'))
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.pharma-dashboard') }}">{{ __('sidebar.home') }}</a></li>
                            @endif
                            <li><i class="ph ph-caret-double-right"></i></li>
                            <li class="breadcrumb-item text-primary active" aria-current="page" id="breadcrumbcustom">
                                {{ __(isset($isEdit) && $isEdit == true ? $edit_module_title ?? $module_title : $module_title ?? '') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
