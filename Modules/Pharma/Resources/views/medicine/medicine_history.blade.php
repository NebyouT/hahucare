@extends('pharma::layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection
@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <h5 class="">{{ __('pharma::messages.medicine_history') }}</h5>

                    <a href="{{ route('backend.medicine.show', $medicine->id) }}" class="btn btn-primary" data-type="ajax"
                        data-bs-toggle="tooltip">
                        {{ __('messages.back') }}
                    </a>
                </div> 
                {{-- Supplier List --}}
                <div class="table-responsive">
                     <table class="table table-custom-border align-middle">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>{{ __('pharma::messages.date') }}</th>
                                <th>{{ __('pharma::messages.batch_no') }}</th>
                                <th>{{ __('pharma::messages.quantity') }}</th>
                                <th>{{ __('pharma::messages.start_serial_no') }}</th>
                                <th>{{ __('pharma::messages.end_serial_no') }}</th>
                                <th>{{ __('pharma::messages.stock_value') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($historyList->isNotEmpty())
                                @foreach($historyList as $history)
                                    <tr>
                                        <td>{{ $history->created_at ? formatDate($history->created_at) : '' }}</td>
                                        <td>{{ $history->batch_no ?? 'N/A' }}</td>
                                        <td>{{ $history->quntity ?? 'N/A' }}</td>
                                        <td>{{ $history->start_serial_no ?? 'N/A' }}</td>
                                        <td>{{ $history->end_serial_no ?? 'N/A' }}</td>
                                        <td>{{ \Currency::format($history->stock_value ?? 0) ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No medicine history found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table> 
                    <table id="datatable" class="table table-responsive">
                    </table>
                </div>
            </div>
        </div>
    </div>


    
@endsection


