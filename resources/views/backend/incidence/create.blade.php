@extends('backend.layouts.app')

@section('title') {{ __('messages.create') }} {{ __('messages.incidence') }} @endsection

@section('content')
<div class="table-content mb-3">
    <x-backend.section-header>
        <div class="d-flex flex-wrap gap-3">
            <h5 class="mb-0">{{ __('messages.create') }} {{ __('messages.incidence') }}</h5>
        </div>
        <x-slot name="toolbar">
            <a href="{{ route('backend.incidence.index') }}" class="btn btn-secondary">
                {{ __('messages.back_to_list') }}
            </a>
        </x-slot>
    </x-backend.section-header>

    <div class="card mt-3">
        <div class="card-body">
            {{ html()->form('POST', route('backend.incidence.store'))->class('form-horizontal')->open() }}
            {{ csrf_field() }}

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.field_title') }} <span class="text-danger">*</span></label>
                        {{ html()->text('title')->class('form-control')->placeholder('Enter title')->required() }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.field_phone_number') }}</label>
                        {{ html()->text('phone')->class('form-control')->placeholder('Enter phone number') }}
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.field_emailid') }}</label>
                        {{ html()->email('email')->class('form-control')->placeholder('Enter email') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.date') }}</label>
                        {{ html()->date('incident_date', date('Y-m-d'))->class('form-control') }}
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.field_description') }} <span class="text-danger">*</span></label>
                        {{ html()->textarea('description')->class('form-control')->placeholder('Enter description')->rows(4)->required() }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end">
                <x-buttons.create title="">
                    {{ __('messages.submit') }}
                </x-buttons.create>
            </div>

            {{ html()->form()->close() }}
        </div>
    </div>
</div>
@endsection
