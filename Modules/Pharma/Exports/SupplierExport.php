<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Pharma\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class SupplierExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;
    public array $dateRange;
    public $exportDoctorId;

    public function __construct($columns, $dateRange, $exportDoctorId = null)
    {
        $this->columns = $columns;
        if (is_string($dateRange) && strpos($dateRange, ' to ') !== false) {
            $this->dateRange = explode(' to ', $dateRange);
        } elseif (is_array($dateRange)) {
            $this->dateRange = $dateRange;
        } else {
            $this->dateRange = [];
        }
        $this->exportDoctorId = $exportDoctorId;
    }

     public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'first_name':
                    $headings[] = __('pharma::messages.first_name');
                    break;
                case 'last_name':
                    $headings[] = __('pharma::messages.last_name');
                    break;
                case 'email':
                    $headings[] = __('messages.email');
                    break;
                case 'contact_number':
                    $headings[] = __('messages.contact_number');
                    break;
                case 'supplier_type':
                    $headings[] = __('pharma::messages.supplier_type');
                    break;
                case 'payment_terms':
                    $headings[] = __('pharma::messages.payment_terms');
                    break;
                case 'status':
                    $headings[] = __('messages.status');
                    break;
                case 'pharma_id':
                    $headings[] = __('multivendor.singular_title');
                    break;
                case 'pharma':
                    $headings[] = __('pharma::messages.pharma');
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
        $query = Supplier::with('supplierType', 'pharmaUser');

        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }
        if (!empty($this->dateRange) && count($this->dateRange) === 2) {
            $query = $query->whereBetween('created_at', [$this->dateRange[0], $this->dateRange[1]]);
        }

        $suppliers = $query->get();

        return $suppliers->map(function ($supplier) {
            $data = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'first_name':
                        $data[] = $supplier->first_name;
                        break;
                    case 'last_name':
                        $data[] = $supplier->last_name;
                        break;
                    case 'email':
                        $data[] = $supplier->email;
                        break;
                    case 'contact_number':
                        $data[] = $supplier->contact_number;
                        break;
                    case 'supplier_type':
                        $data[] = $supplier->supplierType ? $supplier->supplierType->name : '-';
                        break;
                    case 'payment_terms':
                        $data[] = $supplier->payment_terms;
                        break;
                    case 'status':
                        $data[] = $supplier->status ? 'Active' : 'Inactive';
                        break;
                    case 'pharma_id':
                        $data[] = $supplier->pharmaUser ? $supplier->pharmaUser->fullname : '-';
                        break;
                    case 'pharma':
                        $data[] = $supplier->pharmaUser ? $supplier->pharmaUser->fullname : '-';
                        break;
                    default:
                        $data[] = $supplier->{$column} ?? '-';
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
                
                if (!empty($this->dateRange) && count($this->dateRange) === 2) {
                    $sheet->setCellValue('A1', "From Date: {$this->dateRange[0]}");
                    $sheet->setCellValue('A2', "To Date: {$this->dateRange[1]}");
                    $sheet->mergeCells("A1:{$lastColumn}1");
                    $sheet->mergeCells("A2:{$lastColumn}2");
                    $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                    $sheet->getStyle('A1:A2')->getFont()->setSize(12);
                }
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