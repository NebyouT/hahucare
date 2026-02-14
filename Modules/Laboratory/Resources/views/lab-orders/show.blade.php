@extends('backend.layouts.app')

@section('title', 'Lab Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Lab Order Details</h4>
                    <div>
                        @can('edit_lab_orders')
                        <a href="{{ route('backend.lab-orders.edit', $labOrder->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('backend.lab-orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Order Number:</th>
                                    <td>{{ $labOrder->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $labOrder->status === 'completed' ? 'success' : 
                                            ($labOrder->status === 'in_progress' ? 'primary' : 
                                            ($labOrder->status === 'confirmed' ? 'info' : 
                                            ($labOrder->status === 'cancelled' ? 'danger' : 'warning'))) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $labOrder->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>{{ $labOrder->order_date ? $labOrder->order_date->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Confirmed Date:</th>
                                    <td>{{ $labOrder->confirmed_date ? $labOrder->confirmed_date->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Completed Date:</th>
                                    <td>{{ $labOrder->completed_date ? $labOrder->completed_date->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Parties Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Patient:</th>
                                    <td>{{ $labOrder->patient ? $labOrder->patient->full_name : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Doctor:</th>
                                    <td>{{ $labOrder->doctor ? $labOrder->doctor->full_name : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Clinic:</th>
                                    <td>{{ $labOrder->clinic ? $labOrder->clinic->name : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Lab:</th>
                                    <td>{{ $labOrder->lab ? $labOrder->lab->name : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Encounter ID:</th>
                                    <td>{{ $labOrder->encounter_id ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Collection Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Collection Type:</th>
                                    <td>{{ ucfirst($labOrder->collection_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Sample Collection Date:</th>
                                    <td>{{ $labOrder->sample_collection_date ? $labOrder->sample_collection_date->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Collection Notes:</th>
                                    <td>{{ $labOrder->collection_notes ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Financial Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>${{ number_format($labOrder->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Discount Amount:</th>
                                    <td>${{ number_format($labOrder->discount_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Final Amount:</th>
                                    <td><strong>${{ number_format($labOrder->final_amount, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($labOrder->notes)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5>Notes</h5>
                                <p>{{ $labOrder->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <h5>Lab Tests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Final Price</th>
                                    <th>Status</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($labOrder->labOrderItems as $item)
                                <tr>
                                    <td>{{ $item->test_name }}</td>
                                    <td>{{ $item->test_description ?: '-' }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->discount_amount, 2) }}</td>
                                    <td><strong>${{ number_format($item->final_price, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $item->status === 'completed' ? 'success' : 
                                            ($item->status === 'in_progress' ? 'primary' : 'warning') 
                                        }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->labResult)
                                            <a href="{{ route('backend.lab-results.show', $item->labResult->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View Result
                                            </a>
                                        @else
                                            <span class="text-muted">No Result</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
