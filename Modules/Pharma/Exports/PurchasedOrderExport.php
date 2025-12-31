<?php

namespace Modules\Pharma\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Pharma\Models\PurchasedOrder;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class PurchasedOrderExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
                case 'created_at':
                    $headings[] = __('messages.date');
                    break;
                case 'medicine':
                    $headings[] = __('pharma::messages.medicine');
                    break;
                case 'supplier':
                    $headings[] = __('pharma::messages.supplier');
                    break;
                case 'manufacturer':
                    $headings[] = __('pharma::messages.manufacturer');
                    break;
                case 'quantity':
                    $headings[] = __('pharma::messages.quantity');
                    break;
                case 'delivery_date':
                    $headings[] = __('pharma::messages.delivery_date');
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
        $query = PurchasedOrder::with(['medicine.supplier', 'medicine.manufacturer', 'pharmaUser']);

        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }

        $orders = $query->get();

        return $orders->map(function ($order) {
            $data = [];
            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'created_at':
                        $data[] = $order->created_at ? $order->created_at->format('Y-m-d') : '-';
                        break;
                    case 'medicine':
                        $data[] = $order->medicine ? $order->medicine->name : '-';
                        break;
                    case 'supplier':
                        $data[] = $order->medicine && $order->medicine->supplier ? 
                            ($order->medicine->supplier->first_name . ' ' . $order->medicine->supplier->last_name) : '-';
                        break;
                    case 'manufacturer':
                        $data[] = $order->medicine && $order->medicine->manufacturer ? $order->medicine->manufacturer->name : '-';
                        break;
                    case 'quantity':
                        $data[] = $order->quantity ?? '-';
                        break;
                    case 'delivery_date':
                        $data[] = $order->delivery_date ?? '-';
                        break;
                    case 'payment_status':
                        $data[] = $order->payment_status ?? '-';
                        break;
                    case 'pharma_id':
                        $data[] = $order->pharmaUser ? $order->pharmaUser->fullname : '-';
                        break;
                    default:
                        $data[] = $order->{$column} ?? '-';
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