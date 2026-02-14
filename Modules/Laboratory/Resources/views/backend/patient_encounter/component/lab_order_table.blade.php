<div class="table-responsive rounded mb-0">
    <table class="table table-lg m-0" id="lab_order_table">
        <thead>
            <tr class="text-white">
                <th scope="col">{{ __('laboratory.order_number') }}</th>
                <th scope="col">{{ __('laboratory.lab') }}</th>
                <th scope="col">{{ __('laboratory.services') }}</th>
                <th scope="col">{{ __('laboratory.priority') }}</th>
                <th scope="col">{{ __('laboratory.status') }}</th>
                <th scope="col">{{ __('laboratory.order_date') }}</th>
                @if ($data['status'] == 1)
                    <th scope="col">{{ __('appointment.action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (isset($data['lab_orders']) && count($data['lab_orders']) > 0)
                @foreach ($data['lab_orders'] as $labOrder)
                    <tr>
                        <td>
                            <p class="m-0 fw-bold">{{ $labOrder['order_number'] }}</p>
                        </td>
                        <td>
                            <p class="m-0">{{ $labOrder['lab_name'] ?? 'N/A' }}</p>
                        </td>
                        <td>
                            @if (isset($labOrder['services']) && count($labOrder['services']) > 0)
                                @foreach ($labOrder['services'] as $service)
                                    <span class="badge bg-light text-dark me-1">{{ $service['service_name'] }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No services</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $labOrder['priority'] == 'urgent' ? 'danger' : ($labOrder['priority'] == 'stat' ? 'danger' : 'primary') }}">
                                {{ ucfirst($labOrder['priority']) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $labOrder['status'] == 'completed' ? 'success' : ($labOrder['status'] == 'processing' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($labOrder['status']) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($labOrder['order_date'])->format('M d, Y H:i') }}</td>
                        @if ($data['status'] == 1)
                            <td class="action">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($labOrder['status'] == 'completed')
                                        <button type="button" class="btn text-success p-0 fs-5"
                                            onclick="viewLabResults({{ $labOrder['id'] }})"
                                            data-bs-toggle="tooltip"
                                            title="View Results">
                                            <i class="ph ph-clipboard-text"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn text-danger p-0 fs-5"
                                        onclick="destroyLabOrder({{ $labOrder['id'] }}, 'Are you sure you want to delete this lab order?')"
                                        data-bs-toggle="tooltip"
                                        title="Delete Order">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ $data['status'] == 1 ? 7 : 6 }}">
                        <div class="my-1 text-danger text-center no-lab-order-message">
                            {{ __('laboratory.no_lab_orders_found') }}
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

@push('after-scripts')
    <script>
        function destroyLabOrder(id, message) {
            confirmDeleteSwal({
                message
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "/app/encounter/delete-lab-order/" + id,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (response) => {
                        if (response.html) {
                            $('#lab_order_table').html(response.html);
                            Swal.fire({
                                title: 'Deleted',
                                text: response.message,
                                icon: 'success',
                                showClass: {
                                    popup: 'animate__animated animate__zoomIn'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__zoomOut'
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to delete the lab order.',
                                icon: 'error',
                                showClass: {
                                    popup: 'animate__animated animate__shakeX'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut'
                                }
                            });
                        }
                    }
                });
            });
        }

        function viewLabResults(labOrderId) {
            window.open("/app/lab-orders/results/" + labOrderId, '_blank');
        }
    </script>
@endpush
