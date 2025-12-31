<div class="list-group list-group-flush">
    @foreach ($data->commissionData as $item)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1 gap-2 my-2">
                
                <div class="flex-grow-1">
                    {{ $item->mainCommission->title ?? '-' }}
                </div>

                @if (($item->mainCommission->commission_type ?? '') === 'percentage')
                    <div class="flex-grow-1">
                        {{ $item->mainCommission->commission_value }}%
                    </div>
                @else
                    <div class="flex-grow-1">
                        {{ Currency::format(number_format($item->mainCommission->commission_value ?? 0, 2)) }}
                    </div>
                @endif

            </div>
        </div>
    @endforeach
</div>
