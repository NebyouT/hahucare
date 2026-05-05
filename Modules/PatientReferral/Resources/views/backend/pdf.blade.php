<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Referral Letter</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 10pt;
        }
        .referral-info {
            margin-bottom: 20px;
        }
        .referral-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .referral-info td {
            padding: 5px 0;
        }
        .referral-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14pt;
        }
        .content {
            margin-bottom: 15px;
        }
        .encounters {
            margin-top: 20px;
        }
        .encounters table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .encounters th, .encounters td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .encounters th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
        .stamp {
            text-align: center;
            width: 150px;
        }
        .stamp img {
            max-width: 100%;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Patient Referral Letter</h1>
        <p>Referral Code: {{ $referral->referral_code }}</p>
        <p>Date: {{ $referral->referral_date ? $referral->referral_date->format('F j, Y') : 'N/A' }}</p>
    </div>

    <div class="referral-info">
        <table>
            <tr>
                <td>Patient Name:</td>
                <td>{{ $referral->patient ? $referral->patient->first_name . ' ' . $referral->patient->last_name : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Patient Age:</td>
                <td>{{ $referral->patient_age ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Patient Sex:</td>
                <td>{{ $referral->patient_sex ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Patient Address:</td>
                <td>{{ $referral->patient_address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Referred By:</td>
                <td>{{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Referred To:</td>
                <td>{{ $referral->referredToDoctor ? $referral->referredToDoctor->first_name . ' ' . $referral->referredToDoctor->last_name : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Referring Faculty:</td>
                <td>{{ $referral->referring_faculty ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Receiving Faculty:</td>
                <td>{{ $referral->receiving_faculty ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Clinic Name:</td>
                <td>{{ $referral->referring_clinic_name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Chief Complaint</div>
    <div class="content">
        {{ $referral->chief_complaint }}
    </div>

    <div class="section-title">History and Findings</div>
    <div class="content">
        {{ $referral->history_findings ?? 'Not provided' }}
    </div>

    <div class="section-title">Diagnosis</div>
    <div class="content">
        {{ $referral->diagnosis ?? 'Not provided' }}
    </div>

    <div class="section-title">Treatment Given</div>
    <div class="content">
        {{ $referral->treatment_given ?? 'Not provided' }}
    </div>

    <div class="section-title">Investigation Done</div>
    <div class="content">
        {{ $referral->investigation_done ?? 'Not provided' }}
    </div>

    <div class="section-title">Reason for Referral</div>
    <div class="content">
        {{ $referral->reason }}
    </div>

    @if($referral->encounters && $referral->encounters->count() > 0)
    <div class="encounters">
        <div class="section-title">Past Encounters</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach($referral->encounters as $encounter)
                <tr>
                    <td>{{ $encounter->encounter_date ? $encounter->encounter_date->format('F j, Y') : 'N/A' }}</td>
                    <td>{{ $encounter->encounter_type ?? 'General' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($referral->notes)
    <div class="section-title">Additional Notes</div>
    <div class="content">
        {{ $referral->notes }}
    </div>
    @endif

    @if($referral->contact_information)
    <div class="section-title">Contact Information</div>
    <div class="content">
        {{ $referral->contact_information }}
    </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div class="signature-line">
                {{ $referral->referredByDoctor ? $referral->referredByDoctor->first_name . ' ' . $referral->referredByDoctor->last_name : 'N/A' }}
            </div>
            <p>Referring Physician</p>
        </div>
        @if($referralStamp)
        <div class="stamp">
            <img src="{{ asset($referralStamp) }}" alt="Referral Stamp">
        </div>
        @endif
    </div>
</body>
</html>
