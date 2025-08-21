<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private Collection $transactions;
    private array $reportData;

    public function __construct(Collection $transactions, array $reportData)
    {
        $this->transactions = $transactions;
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Data',
            'Descrição',
            'Categoria',
            'Tipo',
            'Valor (R$)',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->date->format('d/m/Y'),
            $transaction->description,
            $transaction->category ? $transaction->category->name : 'N/A',
            $transaction->type === 'income' ? 'Receita' : 'Despesa',
            number_format($transaction->amount, 2, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:E1' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Relatório Financeiro';
    }
}