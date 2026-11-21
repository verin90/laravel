<?php
namespace Tests\Feature\Trade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class TradeTest extends TestCase
{
    use RefreshDatabase;
    private function product(string $sku = 'P1'): int {
        return $this->postJson('/api/products', ['sku' => $sku, 'name' => 'Товар', 'unit' => 'шт', 'retail_price' => '125.50'])
            ->assertCreated()->json('data.id');
    }
    private function department(int $product): int {
        return $this->postJson('/api/departments', [
            'code' => 'D1', 'name' => 'Отдел', 'manager_name' => 'Иванов Иван Иванович',
            'phone' => '+7 000 000-00-00', 'daily_plan' => '1000.00',
            'initial_sale' => ['product_id' => $product, 'sale_date' => '2026-05-01', 'quantity' => '2'],
        ])->assertCreated()->json('data.id');
    }
    public function test_department_requires_a_sale_and_last_sale_is_protected(): void {
        $this->postJson('/api/departments', [])->assertUnprocessable();
        $p = $this->product(); $d = $this->department($p);
        $sale = $this->getJson('/api/sales')->assertOk()->json('data.0.id');
        $this->deleteJson('/api/sales/'.$sale)->assertConflict();
        $this->deleteJson('/api/departments/'.$d)->assertConflict();
        $this->deleteJson('/api/products/'.$p)->assertConflict();
        $this->assertDatabaseCount('departments', 1);
        $this->assertDatabaseCount('sales', 1);
    }
    public function test_crud_history_and_reports(): void {
        $p = $this->product(); $unused = $this->product('UNUSED'); $d = $this->department($p);
        $this->patchJson('/api/products/'.$p, ['retail_price' => '200.00'])->assertOk();
        $this->getJson('/api/sales/1')->assertOk()->assertJsonPath('data.unit_price', '125.50');
        $sale = $this->postJson('/api/sales', ['department_id' => $d, 'product_id' => $p, 'sale_date' => '2026-05-01', 'quantity' => '1'])
            ->assertCreated()->json('data.id');
        $this->patchJson('/api/sales/'.$sale, ['quantity' => '3'])->assertOk();
        $this->getJson('/api/reports/daily?date=2026-05-01')->assertOk()
            ->assertJsonPath('data.0.revenue', '851.00')->assertJsonPath('data.0.completion_percent', '85.10');
        $this->getJson('/api/reports/demand?from=2026-05-01&to=2026-05-01')->assertOk()
            ->assertJsonPath('data.1.sku', 'UNUSED')->assertJsonPath('data.1.sale_count', 0);
        $this->deleteJson('/api/sales/'.$sale)->assertNoContent();
        $this->deleteJson('/api/products/'.$unused)->assertNoContent();
        $this->getJson('/api/products/'.$unused)->assertNotFound();
        $this->postJson('/api/sales', ['department_id' => $d, 'product_id' => 999, 'sale_date' => 'bad', 'quantity' => -1])->assertUnprocessable();
    }
}
