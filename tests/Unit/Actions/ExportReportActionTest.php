<?php

namespace Tests\Unit\Actions;

use App\Actions\ExportReportAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExportReportActionTest extends TestCase
{
    use RefreshDatabase;

    private ExportReportAction $action;
    private User $user;
    private Collection $transactions;
    private array $reportData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ExportReportAction();
        $this->user = User::factory()->create();
        
        // Create test data
        $category1 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Salário']);
        $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Alimentação']);

        $this->transactions = collect([
            Transaction::factory()->create([
                'user_id' => $this->user->id,
                'category_id' => $category1->id,
                'type' => 'income',
                'amount' => 1000,
                'description' => 'Salário Janeiro',
                'date' => '2024-01-15'
            ]),
            Transaction::factory()->create([
                'user_id' => $this->user->id,
                'category_id' => $category2->id,
                'type' => 'expense',
                'amount' => 300,
                'description' => 'Supermercado',
                'date' => '2024-01-20'
            ])
        ]);

        $this->reportData = [
            'totals' => [
                'income' => 1000,
                'expenses' => 300,
                'net' => 700,
                'count' => 2
            ],
            'period' => '01/01/2024 - 31/01/2024'
        ];
    }

    public function test_execute_exports_to_excel_format()
    {
        $filePath = $this->action->execute($this->transactions, $this->reportData, 'excel', 'test_report');

        $this->assertStringContainsString('test_report.xlsx', $filePath);
        $this->assertFileExists($filePath);
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_execute_exports_to_csv_format()
    {
        $filePath = $this->action->execute($this->transactions, $this->reportData, 'csv', 'test_report');

        $this->assertStringContainsString('test_report.csv', $filePath);
        $this->assertFileExists($filePath);
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_execute_exports_to_pdf_format()
    {
        $filePath = $this->action->execute($this->transactions, $this->reportData, 'pdf', 'test_report');

        $this->assertStringContainsString('test_report.html', $filePath);
        $this->assertFileExists($filePath);
        
        // Verify HTML content contains expected data
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('Relatório Financeiro', $content);
        $this->assertStringContainsString('Salário Janeiro', $content);
        $this->assertStringContainsString('Supermercado', $content);
        $this->assertStringContainsString('01/01/2024 - 31/01/2024', $content);
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_execute_generates_filename_when_not_provided()
    {
        $filePath = $this->action->execute($this->transactions, $this->reportData, 'excel');

        $this->assertStringContainsString('relatorio_financeiro_', basename($filePath));
        $this->assertStringContainsString(date('Y-m-d'), basename($filePath));
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_execute_throws_exception_for_unsupported_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato de exportação não suportado: invalid');

        $this->action->execute($this->transactions, $this->reportData, 'invalid');
    }

    public function test_execute_creates_exports_directory_if_not_exists()
    {
        $exportsDir = storage_path('app/exports');
        
        // Remove directory if it exists
        if (is_dir($exportsDir)) {
            array_map('unlink', glob("$exportsDir/*"));
            rmdir($exportsDir);
        }

        $this->assertDirectoryDoesNotExist($exportsDir);

        $filePath = $this->action->execute($this->transactions, $this->reportData, 'excel', 'test_report');

        $this->assertDirectoryExists($exportsDir);
        $this->assertFileExists($filePath);
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function test_get_mime_type_returns_correct_types()
    {
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $this->action->getMimeType('excel')
        );
        
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $this->action->getMimeType('xlsx')
        );
        
        $this->assertEquals('text/csv', $this->action->getMimeType('csv'));
        $this->assertEquals('application/pdf', $this->action->getMimeType('pdf'));
        $this->assertEquals('application/octet-stream', $this->action->getMimeType('unknown'));
    }

    public function test_get_file_extension_returns_correct_extensions()
    {
        $this->assertEquals('xlsx', $this->action->getFileExtension('excel'));
        $this->assertEquals('xlsx', $this->action->getFileExtension('xlsx'));
        $this->assertEquals('csv', $this->action->getFileExtension('csv'));
        $this->assertEquals('html', $this->action->getFileExtension('pdf')); // HTML for now
        $this->assertEquals('txt', $this->action->getFileExtension('unknown'));
    }

    public function test_execute_handles_empty_transactions()
    {
        $emptyTransactions = collect([]);
        $emptyReportData = [
            'totals' => [
                'income' => 0,
                'expenses' => 0,
                'net' => 0,
                'count' => 0
            ],
            'period' => 'Todos os períodos'
        ];

        $filePath = $this->action->execute($emptyTransactions, $emptyReportData, 'excel', 'empty_report');

        $this->assertFileExists($filePath);
        
        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up any remaining test files
        $exportsDir = storage_path('app/exports');
        if (is_dir($exportsDir)) {
            $files = glob("$exportsDir/test_*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::tearDown();
    }
}