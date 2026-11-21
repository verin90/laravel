<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
    public function up(): void {
        Schema::create('trade_locks', fn (Blueprint $t) => $t->unsignedInteger('id')->primary());
        DB::table('trade_locks')->insert(['id' => 1]);
    }
    public function down(): void { Schema::dropIfExists('trade_locks'); }
};
