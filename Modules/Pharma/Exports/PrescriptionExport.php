<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use App\Models\Setting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class PrescriptionExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;
    public array $dateRange;
    public $exportDoctorId;

    public function __construct($columns, $dateRange, $exportDoctorId = null)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
        $this->exportDoctorId = $exportDoctorId;
    }

    public function startCell(): string
    {
        return 'A3'; // Data starts from row 3
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'encounter_id':
                    $headings[] = __('pharma::messages.encounter_id');
                    break;
                case 'created_at':
                    $headings[] = __('messages.date_time');
                    break;
                case 'patient':
                    $headings[] = __('pharma::messages.patient');
                    break;
                case 'doctor':
                    $headings[] = __('pharma::messages.doctor');
                    break;
                case 'clinic':
                    $headings[] = __('pharma::messages.clinic');
                    break;
                case 'total_amount':
                    $headings[] = __('pharma::messages.medicine_total');
                    break;
                case 'prescription_status':
                    $headings[] = __('pharma::messages.prescription_status');
                    break;
                case 'payment_status':
                    $headings[] = __('pharma::messages.payment_status');
                    break;
                case 'pharma_id':
                    $headings[] = __('multivendor.singular_title');
                    break;
                default:
                    $headings[] = ucfirst($column);
                    break;
            }
        }
        return $headings;
    }

    public function collection()
    {
        $query = PatientEncounter::where('patient_encounters.status', 0)
            ->whereHas('prescriptions')
            ->with(['prescriptions.medicine', 'billingrecord', 'user', 'doctor', 'clinic', 'pharmaUser']);

        if (auth()->user()->hasRole('pharma')) {
            $user = auth()->user();
            $query->where('pharma_id', $user->id)
                ->where('patient_encounters.clinic_id', $user->clinic_id);
        }

        $encounters = $query->get();

        return $encounters->map(function ($encounter) {
            $data = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'encounter_id':
                        $data[] = "#" . $encounter->id;
                        break;
                    case 'created_at':
                        $timezone = Setting::where('name', 'default_time_zone')->value('val') ?? 'UTC';
                        $dateFormat = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
                        $timeFormat = Setting::where('name', 'time_formate')->value('val') ?? 'h:i A';
                        $createdAt = Carbon::parse($encounter->created_at)->timezone($timezone);
                        $data[] = $createdAt->format($dateFormat) . ' At ' . $createdAt->format($timeFormat);
                        break;
                    case 'patient':
                        $data[] = $encounter->user ? ($encounter->user->first_name . ' ' . $encounter->user->last_name) : '-';
                        break;
                    case 'doctor':
                        $data[] = $encounter->doctor ? ($encounter->doctor->first_name . ' ' . $encounter->doctor->last_name) : '-';
                        break;
                    case 'clinic':
                        $data[] = $encounter->clinic ? $encounter->clinic->name : '-';
                        break;
                    case 'total_amount':
                        $billingDetail = EncounterPrescriptionBillingDetail::where('encounter_id', $encounter->id)->first();
                        $data[] = $billingDetail ? \Currency::format($billingDetail->total_amount) : '-';
                        break;
                    case 'prescription_status':
                        $data[] = $encounter->prescription_status ? 'Completed' : 'Pending';
                        break;
                    case 'payment_status':
                        $data[] = $encounter->prescription_payment_status ? 'Paid' : 'Unpaid';
                        break;
                    case 'pharma_id':
                        $data[] = $encounter->pharmaUser ? $encounter->pharmaUser->fullname : '-';
                        break;
                    default:
                        $data[] = $encounter->{$column} ?? '-';
                        break;
                }
            }
            return $data;
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A3:' . $event->sheet->getHighestColumn() . '3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'E2E8F0',
                        ],
                    ],
                ]);
            },
        ];
    }
} 