<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Certificate - {{ $medicalCertificate->certificate_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 40px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 18px;
            color: #666;
        }
        .certificate-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            text-decoration: underline;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
        }
        .info-label {
            width: 200px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .section-content {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 50px;
            border-top: 2px solid #000;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
        }
        .date {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="header">
            @if($medicalCertificate->clinic)
            <h1>{{ $medicalCertificate->clinic->clinic_name ?? 'Medical Center' }}</h1>
            @if($medicalCertificate->clinic->address)
            <h2>{{ $medicalCertificate->clinic->address }}</h2>
            @endif
            @endif
        </div>

        <div class="certificate-title">
            MEDICAL CERTIFICATE
        </div>

        <div class="info-row">
            <div class="info-label">Certificate Number:</div>
            <div class="info-value">{{ $medicalCertificate->certificate_number }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Date of Issue:</div>
            <div class="info-value">{{ $medicalCertificate->issue_date ? $medicalCertificate->issue_date->format('F d, Y') : 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Patient Name:</div>
            <div class="info-value">{{ $medicalCertificate->patient ? $medicalCertificate->patient->full_name : 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Certificate Type:</div>
            <div class="info-value">{{ ucfirst(str_replace('_', ' ', $medicalCertificate->certificate_type)) }}</div>
        </div>

        <div class="section">
            <div class="section-title">Certificate Details</div>
            <div class="section-content">
                <p>This is to certify that <strong>{{ $medicalCertificate->patient ? $medicalCertificate->patient->full_name : 'the patient' }}</strong> 
                has been examined at our medical facility and is hereby granted a 
                <strong>{{ ucfirst(str_replace('_', ' ', $medicalCertificate->certificate_type)) }}</strong> 
                certificate for the period specified below.</p>
            </div>
        </div>

        <div class="info-row">
            <div class="info-label">Start Date:</div>
            <div class="info-value">{{ $medicalCertificate->start_date ? $medicalCertificate->start_date->format('F d, Y') : 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">End Date:</div>
            <div class="info-value">{{ $medicalCertificate->end_date ? $medicalCertificate->end_date->format('F d, Y') : 'N/A' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Duration:</div>
            <div class="info-value">{{ $medicalCertificate->duration_days }} day(s)</div>
        </div>

        @if($medicalCertificate->diagnosis)
        <div class="section">
            <div class="section-title">Diagnosis</div>
            <div class="section-content">
                <p>{{ $medicalCertificate->diagnosis }}</p>
            </div>
        </div>
        @endif

        <div class="section">
            <div class="section-title">Reason for Certificate</div>
            <div class="section-content">
                <p>{{ $medicalCertificate->reason }}</p>
            </div>
        </div>

        @if($medicalCertificate->recommendations)
        <div class="section">
            <div class="section-title">Recommendations</div>
            <div class="section-content">
                <p>{{ $medicalCertificate->recommendations }}</p>
            </div>
        </div>
        @endif

        @if($medicalCertificate->notes)
        <div class="section">
            <div class="section-title">Additional Notes</div>
            <div class="section-content">
                <p>{{ $medicalCertificate->notes }}</p>
            </div>
        </div>
        @endif

        <div class="footer">
            <div class="signature">
                <div class="signature-line"></div>
                <div>Doctor's Signature</div>
                <div class="date">{{ $medicalCertificate->doctor ? $medicalCertificate->doctor->full_name : 'N/A' }}</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div>Hospital Stamp</div>
                <div class="date">@if($medicalCertificate->clinic){{ $medicalCertificate->clinic->clinic_name ?? '' }}@endif</div>
            </div>
        </div>
    </div>
</body>
</html>
