{{-- Medical Certificates table shown inside the Patient Encounter page --}}
<div class="table-responsive rounded mb-0">
    <table class="table table-sm align-middle m-0" id="medical_certificate_table">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Certificate #</th>
                <th>Type</th>
                <th>Issue Date</th>
                <th>Duration</th>
                <th>Status</th>
                @if (($data['status'] ?? 0) == 1)
                    <th class="text-end">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $medicalCertificates = \Modules\MedicalCertificate\Models\MedicalCertificate::where('encounter_id', $data->id)
                    ->with(['patient', 'doctor'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            @endphp
            @forelse ($medicalCertificates as $certificate)
                @php
                    $statusColor = match($certificate->status ?? 'draft') {
                        'active'   => 'success',
                        'expired'  => 'warning',
                        'printed'  => 'info',
                        default    => 'secondary',
                    };
                @endphp
                <tr>
                    <td><span class="fw-semibold text-primary">#{{ $certificate->id }}</span></td>
                    <td><span class="fw-semibold">{{ $certificate->certificate_number ?? '—' }}</span></td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ ucfirst(str_replace('_', ' ', $certificate->certificate_type ?? 'N/A')) }}
                        </span>
                    </td>
                    <td class="text-nowrap small text-muted">
                        {{ $certificate->issue_date ? \Carbon\Carbon::parse($certificate->issue_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="text-nowrap small">
                        {{ $certificate->duration_days ?? 0 }} days
                    </td>
                    <td>
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($certificate->status ?? 'draft') }}</span>
                    </td>
                    @if (($data['status'] ?? 0) == 1)
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('print_medical_certificate')
                                <a href="{{ route('backend.medical-certificates.print', $certificate->id) }}" class="btn btn-sm btn-outline-primary py-0 px-1" target="_blank" title="Print">
                                    <i class="ph ph-printer"></i>
                                </a>
                                @endcan
                                @can('print_medical_certificate')
                                <a href="{{ route('backend.medical-certificates.print', $certificate->id) }}" class="btn btn-sm btn-outline-success py-0 px-1" target="_blank" title="Download">
                                    <i class="ph ph-download-simple"></i>
                                </a>
                                @endcan
                                @can('edit_medical_certificate')
                                <a href="{{ route('backend.medical-certificates.edit', $certificate->id) }}" class="btn btn-sm btn-outline-warning py-0 px-1" title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        <i class="ph ph-file-text me-1"></i> No medical certificates yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
