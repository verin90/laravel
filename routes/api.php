<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{TradeController, ReportController};
Route::get('reports/daily', [ReportController::class, 'daily']);
Route::get('reports/demand', [ReportController::class, 'demand']);
foreach (['departments', 'products', 'sales'] as $entity) {
    Route::get($entity, [TradeController::class, 'index'])->defaults('entity', $entity);
    Route::post($entity, [TradeController::class, 'store'])->defaults('entity', $entity);
    Route::get($entity.'/{id}', [TradeController::class, 'show'])->whereNumber('id')->defaults('entity', $entity);
    Route::match(['put', 'patch'], $entity.'/{id}', [TradeController::class, 'update'])->whereNumber('id')->defaults('entity', $entity);
    Route::delete($entity.'/{id}', [TradeController::class, 'destroy'])->whereNumber('id')->defaults('entity', $entity);
}
