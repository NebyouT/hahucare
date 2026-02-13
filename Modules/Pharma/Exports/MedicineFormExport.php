<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Pharma\Models\MedicineForm;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class MedicineFormExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
                case 'name':
                    $headings[] = __('messages.name');
                    break;
                case 'status':
                    $headings[] = __('messages.status');
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
        $query = MedicineForm::query();

        $forms = $query->get();

        return $forms->map(function ($form) {
            $data = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'name':
                        $data[] = $form->name;
                        break;
                    case 'status':
                        $data[] = $form->status ? 'Active' : 'Inactive';
                        break;
                    default:
                        $data[] = $form->{$column} ?? '-';
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
                // Show date range in first row, cell A1
                $dateRangeText = 'Date Range: ' . implode(' to ', $this->dateRange);
                $event->sheet->setCellValue('A1', $dateRangeText);

                // Merge cells for date range
                $highestColumn = $event->sheet->getHighestColumn();
                $event->sheet->mergeCells("A1:{$highestColumn}1");

                // Style for date range row
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Style for heading row
                $event->sheet->getStyle('A3:' . $highestColumn . '3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12, // Larger font for headings
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'E2E8F0',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Set column widths large for PDF readability
                foreach (range('A', $highestColumn) as $col) {
                    $event->sheet->getColumnDimension($col)->setWidth(30); // Adjust width as needed
                }

                // Set font size for all data rows
                $rowCount = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A4:{$highestColumn}{$rowCount}")->applyFromArray([
                    'font' => [
                        'size' => 11, // Larger font for data
                    ],
                ]);
            },
        ];
    }
} 