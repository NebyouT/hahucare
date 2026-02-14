<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Pharma\Models\Medicine;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Carbon\Carbon;

class MedicineExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
                case 'dosage':
                    $headings[] = __('pharma::messages.dosage');
                    break;
                case 'form':
                    $headings[] = __('pharma::messages.form');
                    break;
                case 'category':
                    $headings[] = __('pharma::messages.category');
                    break;
                case 'supplier':
                    $headings[] = __('pharma::messages.supplier');
                    break;
                case 'manufacturer':
                    $headings[] = __('pharma::messages.manufacturer');
                    break;
                case 'expiry_date':
                    $headings[] = __('pharma::messages.expiry_date');
                    break;
                case 'selling_price':
                    $headings[] = __('pharma::messages.selling_price');
                    break;
                case 'quantity':
                    $headings[] = __('pharma::messages.quantity');
                    break;
                case 'status':
                    $headings[] = __('messages.status');
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
        $query = Medicine::with(['category', 'form', 'supplier', 'manufacturer', 'pharmaUser'])->where('expiry_date', '>=', Carbon::today());

        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }

        $medicines = $query->get();

        return $medicines->map(function ($medicine) {
            $data = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'name':
                        $data[] = $medicine->name;
                        break;
                    case 'dosage':
                        $data[] = $medicine->dosage;
                        break;
                    case 'form':
                        $data[] = $medicine->form ? $medicine->form->name : '-';
                        break;
                    case 'category':
                        $data[] = $medicine->category ? $medicine->category->name : '-';
                        break;
                    case 'supplier':
                        $data[] = $medicine->supplier ? $medicine->supplier->full_name : '-';
                        break;
                    case 'manufacturer':
                        $data[] = $medicine->manufacturer ? $medicine->manufacturer->name : '-';
                        break;
                    case 'expiry_date':
                        $data[] = $medicine->expiry_date ? $medicine->expiry_date->format('Y-m-d') : '-';
                        break;
                    case 'selling_price':
                        $data[] = $medicine->selling_price ? \Currency::format($medicine->selling_price) : '-';
                        break;
                    case 'quantity':
                        $data[] = $medicine->quantity ?? '-';
                        break;
                    case 'status':
                        $data[] = $medicine->status ? 'Active' : 'Inactive';
                        break;
                    case 'pharma_id':
                        $data[] = $medicine->pharmaUser ? $medicine->pharmaUser->fullname : '-';
                        break;
                    default:
                        $data[] = $medicine->{$column} ?? '-';
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
                $sheet = $event->sheet->getDelegate();
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));

                $sheet->setCellValue('A1', "From Date: {$this->dateRange[0]}");
                $sheet->setCellValue('A2', "To Date: {$this->dateRange[1]}");

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A1:A2')->getFont()->setSize(12);
            },
        ];
    }
} 