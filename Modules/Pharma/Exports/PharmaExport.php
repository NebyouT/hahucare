<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use app\Models\User;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class PharmaExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;
    public array $dateRange;

    public function __construct($columns, $dateRange)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
    }
    public function startCell(): string
    {
        return 'A3'; // Data starts from row 3
    }
    public function headings(): array
    {
        $modifiedHeadings = [];

        foreach ($this->columns as $column) {
            // Capitalize each word and replace underscores with spaces
            $modifiedHeadings[] = ucwords(str_replace('_', ' ', $column));
        }

        return $modifiedHeadings;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = User::with('clinic')->where('user_type', 'pharma');
        if (!empty($this->dateRange)) {
            $query->whereDate('created_at', '>=', $this->dateRange[0]);
            $query->whereDate('created_at', '<=', $this->dateRange[1]);
        }
        $pharmas = $query->get();

        $newQuery = $pharmas->map(function ($row) {
            $selectedData = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'pharma_name':
                        $selectedData[$column] = ($row->first_name ?? '') . ' ' . ($row->last_name ?? '');
                        break;
                    case 'clinic_name':
                        $selectedData[$column] = $row->clinic ? $row->clinic->name : '';
                        break;
                    case 'status':
                        $selectedData[$column] = $row->status ? 'Active' : 'Inactive';
                        break;
                    case 'verification_status':
                        $selectedData[$column] = $row->email_verified_at ? 'Verified' : 'Not Verified';
                        break;
                    default:
                        $selectedData[$column] = $row->{$column} ?? '';
                        break;
                }
            }
            return $selectedData;
        });
        return $newQuery;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));

                // Add "From Date" and "To Date" at the top
                $sheet->setCellValue('A1', "From Date: {$this->dateRange[0]}");
                $sheet->setCellValue('A2', "To Date: {$this->dateRange[1]}");

                // Merge cells for a cleaner header
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                // Style the headers (optional)
                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A1:A2')->getFont()->setSize(12);
            },
        ];
    }
} 