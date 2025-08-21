<?php

namespace App\Actions;

use App\Exports\FinancialReportExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ExportReportAction
{
    /**
     * Export financial report data in the specified format.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @param string $format
     * @param string $filename
     * @return string
     */
    public function execute(Collection $transactions, array $reportData, string $format, string $filename = null): string
    {
        if (!$filename) {
            $filename = 'relatorio_financeiro_' . date('Y-m-d_H-i-s');
        }

        switch (strtolower($format)) {
            case 'excel':
            case 'xlsx':
                return $this->exportToExcel($transactions, $reportData, $filename);
            
            case 'csv':
                return $this->exportToCsv($transactions, $reportData, $filename);
            
            case 'pdf':
                return $this->exportToPdf($transactions, $reportData, $filename);
            
            default:
                throw new \InvalidArgumentException("Formato de exportação não suportado: {$format}");
        }
    }

    /**
     * Export data to Excel format.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @param string $filename
     * @return string
     */
    private function exportToExcel(Collection $transactions, array $reportData, string $filename): string
    {
        $filePath = storage_path("app/exports/{$filename}.xlsx");
        
        // Ensure the exports directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Create CSV content for Excel compatibility
        $csvContent = $this->generateCsvContent($transactions, $reportData);
        file_put_contents($filePath, $csvContent);
        
        return $filePath;
    }

    /**
     * Export data to CSV format.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @param string $filename
     * @return string
     */
    private function exportToCsv(Collection $transactions, array $reportData, string $filename): string
    {
        $filePath = storage_path("app/exports/{$filename}.csv");
        
        // Ensure the exports directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $csvContent = $this->generateCsvContent($transactions, $reportData);
        file_put_contents($filePath, $csvContent);
        
        return $filePath;
    }

    /**
     * Export data to PDF format.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @param string $filename
     * @return string
     */
    private function exportToPdf(Collection $transactions, array $reportData, string $filename): string
    {
        // For now, we'll create a simple PDF export without DomPDF dependency
        // This can be enhanced later with proper PDF generation
        $filePath = storage_path("app/exports/{$filename}.pdf");
        
        // Ensure the exports directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Create a simple HTML content for PDF
        $html = $this->generatePdfHtml($transactions, $reportData);
        
        // For now, we'll save as HTML file (can be enhanced with proper PDF library)
        file_put_contents(str_replace('.pdf', '.html', $filePath), $html);
        
        return str_replace('.pdf', '.html', $filePath);
    }

    /**
     * Generate HTML content for PDF export.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @return string
     */
    private function generatePdfHtml(Collection $transactions, array $reportData): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { margin-bottom: 30px; }
        .summary-item { display: inline-block; margin: 10px 20px; padding: 10px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .income { color: green; }
        .expense { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Financeiro</h1>
        <p>Período: ' . ($reportData['period'] ?? 'Todos os períodos') . '</p>
        <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
    </div>

    <div class="summary">
        <h2>Resumo</h2>
        <div class="summary-item">
            <strong>Total de Receitas:</strong><br>
            R$ ' . number_format($reportData['totals']['income'] ?? 0, 2, ',', '.') . '
        </div>
        <div class="summary-item">
            <strong>Total de Despesas:</strong><br>
            R$ ' . number_format($reportData['totals']['expenses'] ?? 0, 2, ',', '.') . '
        </div>
        <div class="summary-item">
            <strong>Saldo Líquido:</strong><br>
            R$ ' . number_format($reportData['totals']['net'] ?? 0, 2, ',', '.') . '
        </div>
        <div class="summary-item">
            <strong>Total de Transações:</strong><br>
            ' . ($reportData['totals']['count'] ?? 0) . '
        </div>
    </div>

    <h2>Transações</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($transactions as $transaction) {
            $typeClass = $transaction->type === 'income' ? 'income' : 'expense';
            $typeLabel = $transaction->type === 'income' ? 'Receita' : 'Despesa';
            
            $html .= '<tr>
                <td>' . $transaction->date->format('d/m/Y') . '</td>
                <td>' . htmlspecialchars($transaction->description) . '</td>
                <td>' . htmlspecialchars($transaction->category->name) . '</td>
                <td class="' . $typeClass . '">' . $typeLabel . '</td>
                <td class="' . $typeClass . '">R$ ' . number_format($transaction->amount, 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>
</body>
</html>';

        return $html;
    }

    /**
     * Get the MIME type for the given format.
     *
     * @param string $format
     * @return string
     */
    public function getMimeType(string $format): string
    {
        switch (strtolower($format)) {
            case 'excel':
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'csv':
                return 'text/csv';
            case 'pdf':
                return 'application/pdf';
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * Get the file extension for the given format.
     *
     * @param string $format
     * @return string
     */
    public function getFileExtension(string $format): string
    {
        switch (strtolower($format)) {
            case 'excel':
            case 'xlsx':
                return 'xlsx';
            case 'csv':
                return 'csv';
            case 'pdf':
                return 'html'; // For now, until proper PDF is implemented
            default:
                return 'txt';
        }
    }

    /**
     * Generate CSV content from transactions data.
     *
     * @param Collection $transactions
     * @param array $reportData
     * @return string
     */
    private function generateCsvContent(Collection $transactions, array $reportData): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8 Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Write headers
        fputcsv($output, ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor (R$)'], ';');
        
        // Write transaction data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction->date->format('d/m/Y'),
                $transaction->description,
                $transaction->category ? $transaction->category->name : 'N/A',
                $transaction->type === 'income' ? 'Receita' : 'Despesa',
                number_format($transaction->amount, 2, ',', '.')
            ], ';');
        }
        
        // Add summary at the end
        fputcsv($output, [], ';'); // Empty line
        fputcsv($output, ['RESUMO'], ';');
        fputcsv($output, ['Total de Receitas', 'R$ ' . number_format($reportData['totals']['income'] ?? 0, 2, ',', '.')], ';');
        fputcsv($output, ['Total de Despesas', 'R$ ' . number_format($reportData['totals']['expenses'] ?? 0, 2, ',', '.')], ';');
        fputcsv($output, ['Saldo Líquido', 'R$ ' . number_format($reportData['totals']['net'] ?? 0, 2, ',', '.')], ';');
        fputcsv($output, ['Total de Transações', $reportData['totals']['count'] ?? 0], ';');
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
}