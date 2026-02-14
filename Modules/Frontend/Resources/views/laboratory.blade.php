@extends('frontend::layouts.master')

@section('title', 'Laboratory Services')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Laboratory Services</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Laboratory</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Laboratory Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills mb-4" id="laboratoryTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="labs-tab" data-bs-toggle="pill" data-bs-target="#labs" type="button" role="tab" aria-controls="labs" aria-selected="true">
                                <i class="ph ph-building-office me-2"></i>Labs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tests-tab" data-bs-toggle="pill" data-bs-target="#tests" type="button" role="tab" aria-controls="tests" aria-selected="false">
                                <i class="ph ph-test-tube me-2"></i>Lab Tests
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="pill" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="false">
                                <i class="ph ph-folder me-2"></i>Categories
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="laboratoryTabContent">
                        <!-- Labs Tab -->
                        <div class="tab-pane fade show active" id="labs" role="tabpanel" aria-labelledby="labs-tab">
                            <div class="row">
                                @if($labs->count() > 0)
                                    @foreach($labs as $lab)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100 lab-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-3">
                                                            <i class="ph ph-building-office text-primary fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="card-title mb-1">{{ $lab->name }}</h5>
                                                            @if($lab->clinic)
                                                                <p class="text-muted mb-0">{{ $lab->clinic->name }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="card-text">{{ Str::limit($lab->description, 100) }}</p>
                                                    
                                                    <div class="lab-info">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ph ph-phone me-2 text-muted"></i>
                                                            <span>{{ $lab->phone ?? 'N/A' }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ph ph-envelope me-2 text-muted"></i>
                                                            <span>{{ $lab->email ?? 'N/A' }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ph ph-map-pin me-2 text-muted"></i>
                                                            <span>{{ $lab->address ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        @if($lab->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="ph ph-building-office text-muted fs-1 mb-3"></i>
                                            <h5 class="text-muted">No labs found</h5>
                                            <p class="text-muted">There are currently no laboratories available.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Lab Tests Tab -->
                        <div class="tab-pane fade" id="tests" role="tabpanel" aria-labelledby="tests-tab">
                            <div class="row">
                                @if($tests->count() > 0)
                                    @foreach($tests as $test)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100 test-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <h5 class="card-title">{{ $test->test_name }}</h5>
                                                        <div class="text-end">
                                                            <h6 class="text-primary mb-0">${{ number_format($test->final_price, 2) }}</h6>
                                                            @if($test->discount_price && $test->discount_price < $test->price)
                                                                <small class="text-muted text-decoration-line-through">${{ number_format($test->price, 2) }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="card-text">{{ Str::limit($test->description, 100) }}</p>
                                                    
                                                    <div class="test-info">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ph ph-folder me-2 text-muted"></i>
                                                            <span>{{ $test->category->name ?? 'Uncategorized' }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ph ph-clock me-2 text-muted"></i>
                                                            <span>{{ $test->duration_minutes ?? 'N/A' }} mins</span>
                                                        </div>
                                                        @if($test->lab)
                                                            <div class="d-flex align-items-center">
                                                                <i class="ph ph-building-office me-2 text-muted"></i>
                                                                <span>{{ $test->lab->name }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        @if($test->is_active)
                                                            <span class="badge bg-success">Available</span>
                                                        @else
                                                            <span class="badge bg-secondary">Unavailable</span>
                                                        @endif
                                                        @if($test->is_featured)
                                                            <span class="badge bg-warning">Featured</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="ph ph-test-tube text-muted fs-1 mb-3"></i>
                                            <h5 class="text-muted">No lab tests found</h5>
                                            <p class="text-muted">There are currently no lab tests available.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Categories Tab -->
                        <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                            <div class="row">
                                @if($categories->count() > 0)
                                    @foreach($categories as $category)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100 category-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="avatar-sm bg-info bg-opacity-10 rounded-circle me-3">
                                                            <i class="ph ph-folder text-info fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="card-title mb-1">{{ $category->name }}</h5>
                                                            <p class="text-muted mb-0">{{ $category->lab_tests->count() }} tests</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="card-text">{{ Str::limit($category->description, 100) }}</p>
                                                    
                                                    <div class="mt-3">
                                                        @if($category->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-5">
                                            <i class="ph ph-folder text-muted fs-1 mb-3"></i>
                                            <h5 class="text-muted">No categories found</h5>
                                            <p class="text-muted">There are currently no lab test categories available.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.lab-card, .test-card, .category-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.lab-card:hover, .test-card:hover, .category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.lab-info, .test-info {
    font-size: 0.9rem;
}

.avatar-sm {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
