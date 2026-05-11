<table class="table table-bordered table-striped mb-0">
    <thead>
        <tr>
            <th>{{ __('messages.id') }}</th>
            <th>{{ __('medicalcertificate.certificate_number') }}</th>
            <th>{{ __('medicalcertificate.type') }}</th>
            <th>{{ __('medicalcertificate.issue_date') }}</th>
            <th>{{ __('medicalcertificate.duration') }}</th>
            <th>{{ __('messages.status') }}</th>
            <th>{{ __('messages.action') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $medicalCertificates = \Modules\MedicalCertificate\Models\MedicalCertificate::where('encounter_id', $data->id)
                ->with(['patient', 'doctor'])
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp
        @if($medicalCertificates->count() > 0)
            @foreach($medicalCertificates as $certificate)
            <tr>
                <td>{{ $certificate->id }}</td>
                <td>{{ $certificate->certificate_number }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $certificate->certificate_type)) }}</td>
                <td>{{ $certificate->issue_date ? $certificate->issue_date->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $certificate->duration_days }} {{ __('messages.days') }}</td>
                <td>
                    @switch($certificate->status)
                        @case('draft')
                            <span class="badge badge-secondary">{{ ucfirst($certificate->status) }}</span>
                            @break
                        @case('active')
                            <span class="badge badge-success">{{ ucfirst($certificate->status) }}</span>
                            @break
                        @case('expired')
                            <span class="badge badge-warning">{{ ucfirst($certificate->status) }}</span>
                            @break
                        @case('printed')
                            <span class="badge badge-info">{{ ucfirst($certificate->status) }}</span>
                            @break
                        @default
                            <span class="badge badge-secondary">{{ ucfirst($certificate->status) }}</span>
                    @endswitch
                </td>
                <td>
                    <div class="d-flex gap-1">
                        @can('print_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.print', $certificate->id) }}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                        @endcan
                        @can('print_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.print', $certificate->id) }}" class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                        @endcan
                        @can('edit_medical_certificate')
                        <a href="{{ route('backend.medical-certificates.edit', $certificate->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7" class="text-center">{{ __('messages.no_data_available') }}</td>
            </tr>
        @endif
    </tbody>
</table>
