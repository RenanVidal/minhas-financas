<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_authenticated_user_can_view_reports_index()
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('filters');
        $response->assertViewHas('reportData', null);
    }

    public function test_unauthenticated_user_cannot_view_reports_index()
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_generate_report_with_filters()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'category_id' => $this->category->id,
            'type' => 'income'
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('reportData');
        
        $reportData = $response->viewData('reportData');
        $this->assertTrue($reportData['has_data']);
        $this->assertCount(1, $reportData['transactions']);
        $this->assertEquals(1000, $reportData['totals']['income']);
    }

    public function test_generate_report_validates_date_range()
    {
        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-31',
            'end_date' => '2024-01-01', // End date before start date
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_generate_report_validates_category_ownership()
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'category_id' => $otherCategory->id,
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('error', 'Categoria não encontrada.');
    }

    public function test_authenticated_user_can_export_report_excel()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'excel'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_authenticated_user_can_export_report_csv()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-15'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'csv'
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_authenticated_user_can_export_report_pdf()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'pdf'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_validates_required_format()
    {
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            // Missing format
        ]);

        $response->assertSessionHasErrors('format');
    }

    public function test_export_validates_format_values()
    {
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'invalid_format'
        ]);

        $response->assertSessionHasErrors('format');
    }

    public function test_export_redirects_when_no_data_to_export()
    {
        // No transactions created
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'excel'
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('warning', 'Não há dados para exportar no período selecionado.');
    }

    public function test_export_validates_category_ownership()
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'category_id' => $otherCategory->id,
            'format' => 'excel'
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('error', 'Categoria não encontrada.');
    }

    public function test_reports_index_shows_only_user_categories()
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other Category']);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        
        $this->assertCount(1, $categories);
        $this->assertEquals($this->category->id, $categories->first()->id);
        $this->assertNotContains('Other Category', $categories->pluck('name'));
    }

    public function test_generate_report_returns_empty_result_when_no_transactions()
    {
        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');
        
        $this->assertFalse($reportData['has_data']);
        $this->assertEmpty($reportData['transactions']);
        $this->assertEquals(0, $reportData['totals']['count']);
    }

    public function test_generate_report_filters_by_transaction_type()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-20'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'type' => 'income'
        ]);

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');
        
        $this->assertTrue($reportData['has_data']);
        $this->assertCount(1, $reportData['transactions']);
        $this->assertEquals('income', $reportData['transactions']->first()->type);
        $this->assertEquals(1000, $reportData['totals']['income']);
        $this->assertEquals(0, $reportData['totals']['expenses']);
    }

    // Requisito 5.1: Permitir filtrar por período, categoria e tipo de transação
    public function test_reports_index_displays_filter_interface()
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('name="start_date"', false);
        $response->assertSee('name="end_date"', false);
        $response->assertSee('name="category_id"', false);
        $response->assertSee('name="type"', false);
        $response->assertSee('Gerar Relatório');
        $response->assertViewHas('categories');
    }

    // Requisito 5.1: Filtros funcionam corretamente
    public function test_generate_report_applies_all_filters_correctly()
    {
        $category1 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Category 1']);
        $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Category 2']);

        // Create transactions with different attributes
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15',
            'description' => 'Filtered Transaction'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-20',
            'description' => 'Other Transaction'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 800,
            'date' => '2024-02-15', // Different month
            'description' => 'Out of Range Transaction'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'category_id' => $category1->id,
            'type' => 'income'
        ]);

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');
        
        $this->assertTrue($reportData['has_data']);
        $this->assertCount(1, $reportData['transactions']);
        $this->assertEquals('Filtered Transaction', $reportData['transactions']->first()->description);
    }

    // Requisito 5.2: Exibir tabela com todas as transações do período selecionado
    public function test_generate_report_displays_transactions_table()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15',
            'description' => 'Test Income'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-20',
            'description' => 'Test Expense'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Test Income');
        $response->assertSee('Test Expense');
        $response->assertSee('R$ 1.000,00');
        $response->assertSee('R$ 500,00');
        $response->assertSee('15/01/2024');
        $response->assertSee('20/01/2024');
    }

    // Requisito 5.3: Mostrar totais por categoria e tipo de transação
    public function test_generate_report_displays_totals_by_category_and_type()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Food',
            'type' => 'expense'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 3000,
            'date' => '2024-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 2000,
            'date' => '2024-01-20'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 800,
            'date' => '2024-01-25'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');
        
        $this->assertEquals(5000, $reportData['totals']['income']); // 3000 + 2000
        $this->assertEquals(800, $reportData['totals']['expenses']);
        $this->assertEquals(4200, $reportData['totals']['net']); // 5000 - 800
        $this->assertEquals(3, $reportData['totals']['count']);
        
        // Check that totals are calculated correctly
        $this->assertArrayHasKey('income', $reportData['totals']);
        $this->assertArrayHasKey('expenses', $reportData['totals']);
        $this->assertArrayHasKey('net', $reportData['totals']);
        $this->assertArrayHasKey('count', $reportData['totals']);
    }

    // Requisito 5.4: Permitir exportar dados em formato PDF ou Excel
    public function test_export_supports_all_required_formats()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        // Test Excel export
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'excel'
        ]);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Test CSV export
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'csv'
        ]);
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        // Test PDF export
        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'format' => 'pdf'
        ]);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // Requisito 5.5: Exibir mensagem informativa quando período não contém transações
    public function test_generate_report_shows_message_for_empty_period()
    {
        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
        $reportData = $response->viewData('reportData');
        
        $this->assertFalse($reportData['has_data']);
        $this->assertEmpty($reportData['transactions']);
        $this->assertEquals(0, $reportData['totals']['count']);
        
        $response->assertSee('Nenhuma transação encontrada');
    }

    // Teste adicional: Validação de datas
    public function test_generate_report_validates_date_format()
    {
        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => 'invalid-date',
            'end_date' => '2024-01-31',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    // Teste adicional: Filtro por período funciona corretamente
    public function test_generate_report_filters_by_date_range_correctly()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'date' => '2023-12-31', // Before range
            'description' => 'Before Range'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'date' => '2024-01-15', // In range
            'description' => 'In Range'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'date' => '2024-02-01', // After range
            'description' => 'After Range'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.generate'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        $response->assertStatus(200);
        $response->assertSee('In Range');
        $response->assertDontSee('Before Range');
        $response->assertDontSee('After Range');
        
        $reportData = $response->viewData('reportData');
        $this->assertCount(1, $reportData['transactions']);
    }

    // Teste adicional: Exportação com filtros aplicados
    public function test_export_applies_same_filters_as_report_generation()
    {
        $category1 = Category::factory()->create(['user_id' => $this->user->id]);
        $category2 = Category::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-20'
        ]);

        $response = $this->actingAs($this->user)->post(route('reports.export'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'category_id' => $category1->id,
            'type' => 'income',
            'format' => 'excel'
        ]);

        $response->assertStatus(200);
        // The export should only include the filtered transaction
    }

    // Teste adicional: Proteção contra acesso não autorizado
    public function test_reports_require_authentication()
    {
        $response = $this->get(route('reports.index'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('reports.generate'), []);
        $response->assertRedirect(route('login'));

        $response = $this->post(route('reports.export'), []);
        $response->assertRedirect(route('login'));
    }
}