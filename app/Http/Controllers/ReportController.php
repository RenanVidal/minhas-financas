<?php

namespace App\Http\Controllers;

use App\Actions\ExportReportAction;
use App\Actions\GenerateFinancialReportAction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    private GenerateFinancialReportAction $generateReportAction;
    private ExportReportAction $exportReportAction;

    public function __construct(
        GenerateFinancialReportAction $generateReportAction,
        ExportReportAction $exportReportAction
    ) {
        $this->generateReportAction = $generateReportAction;
        $this->exportReportAction = $exportReportAction;
    }

    /**
     * Display the reports interface with filters.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $categories = $user->categories()->orderBy('name')->get();
        
        // Get filter values from request
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'category_id' => $request->get('category_id'),
            'type' => $request->get('type'),
        ];

        // Generate report if filters are applied
        $reportData = null;
        if ($this->hasFilters($filters)) {
            $reportData = $this->generateReportAction->execute($user, $filters);
        }

        return view('reports.index', compact('categories', 'filters', 'reportData'));
    }

    /**
     * Generate and display the financial report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'nullable|in:income,expense',
        ]);

        $user = auth()->user();
        $categories = $user->categories()->orderBy('name')->get();
        
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'category_id' => $request->get('category_id'),
            'type' => $request->get('type'),
        ];

        // Validate that category belongs to user if specified
        if ($filters['category_id']) {
            $category = Category::where('id', $filters['category_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$category) {
                return redirect()->route('reports.index')
                    ->with('error', 'Categoria não encontrada.');
            }
        }

        $reportData = $this->generateReportAction->execute($user, $filters);

        return view('reports.index', compact('categories', 'filters', 'reportData'));
    }

    /**
     * Export the financial report in the specified format.
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'nullable|in:income,expense',
            'format' => 'required|in:excel,csv,pdf',
        ]);

        $user = auth()->user();
        
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'category_id' => $request->get('category_id'),
            'type' => $request->get('type'),
        ];

        // Validate that category belongs to user if specified
        if ($filters['category_id']) {
            $category = Category::where('id', $filters['category_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$category) {
                return redirect()->route('reports.index')
                    ->with('error', 'Categoria não encontrada.');
            }
        }

        $reportData = $this->generateReportAction->execute($user, $filters);
        
        if (!$reportData['has_data']) {
            return redirect()->route('reports.index')
                ->with('warning', 'Não há dados para exportar no período selecionado.');
        }

        $format = $request->get('format');
        $filename = 'relatorio_financeiro_' . date('Y-m-d_H-i-s');
        
        try {
            $filePath = $this->exportReportAction->execute(
                $reportData['transactions'],
                $reportData,
                $format,
                $filename
            );

            $mimeType = $this->exportReportAction->getMimeType($format);
            $extension = $this->exportReportAction->getFileExtension($format);
            $downloadName = "{$filename}.{$extension}";

            return Response::download($filePath, $downloadName, [
                'Content-Type' => $mimeType,
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->route('reports.index')
                ->with('error', 'Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Check if any filters are applied.
     */
    private function hasFilters(array $filters): bool
    {
        return !empty($filters['start_date']) || 
               !empty($filters['end_date']) || 
               !empty($filters['category_id']) || 
               !empty($filters['type']);
    }
}