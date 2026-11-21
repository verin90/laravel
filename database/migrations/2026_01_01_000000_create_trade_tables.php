<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('code', 30)->unique(); $t->string('name');
            $t->string('manager_name'); $t->string('phone', 30);
            $t->decimal('daily_plan', 14, 2); $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->string('sku', 50)->unique(); $t->string('name');
            $t->string('unit', 30); $t->decimal('retail_price', 14, 2); $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->restrictOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->date('sale_date'); $t->decimal('quantity', 12, 3);
            $t->decimal('unit_price', 14, 2); $t->timestamps();
            $t->index(['department_id', 'sale_date']);
            $t->index(['product_id', 'sale_date']);
        });
        DB::statement('ALTER TABLE departments ADD CONSTRAINT chk_plan CHECK (daily_plan >= 0)');
        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_price CHECK (retail_price >= 0)');
        DB::statement('ALTER TABLE sales ADD CONSTRAINT chk_sale CHECK (quantity > 0 AND unit_price >= 0)');
    }
    public function down(): void {
        Schema::dropIfExists('sales'); Schema::dropIfExists('products'); Schema::dropIfExists('departments');
    }
};
