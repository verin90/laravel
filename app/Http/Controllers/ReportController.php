<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller
{
    public function daily(Request $request) {
        $v = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);
        $totals = DB::table('sales')->selectRaw('department_id, SUM(ROUND(quantity * unit_price, 2)) AS revenue')
            ->where('sale_date', $v['date'])->groupBy('department_id');
        $rows = DB::table('departments AS d')->leftJoinSub($totals, 's', 's.department_id', '=', 'd.id')
            ->select('d.id', 'd.code', 'd.name', 'd.daily_plan')
            ->selectRaw('COALESCE(s.revenue, 0) AS revenue, COALESCE(s.revenue, 0) - d.daily_plan AS difference')
            ->selectRaw('ROUND(COALESCE(s.revenue, 0) / NULLIF(d.daily_plan, 0) * 100, 2) AS completion_percent')
            ->orderBy('d.id')->get();
        return response()->json(['date' => $v['date'], 'data' => $rows]);
    }
    public function demand(Request $request) {
        $v = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $totals = DB::table('sales')->whereBetween('sale_date', [$v['from'], $v['to']])->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) AS quantity, SUM(ROUND(quantity * unit_price, 2)) AS revenue, COUNT(*) AS sale_count');
        $rows = DB::table('products AS p')->leftJoinSub($totals, 's', 's.product_id', '=', 'p.id')
            ->select('p.id', 'p.sku', 'p.name', 'p.unit')
            ->selectRaw('COALESCE(s.quantity, 0) AS quantity, COALESCE(s.revenue, 0) AS revenue, COALESCE(s.sale_count, 0) AS sale_count')
            ->orderByDesc('sale_count')->orderBy('p.id')->get();
        return response()->json(['from' => $v['from'], 'to' => $v['to'], 'data' => $rows]);
    }
}
