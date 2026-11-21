<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TradeRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        $entity = $this->route('entity');
        $creating = $this->isMethod('post');
        $presence = $creating || $this->isMethod('put') ? 'required' : 'sometimes';
        $id = $this->route('id');
        $sale = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'sale_date' => ['required', 'date_format:Y-m-d'],
            'quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
        ];
        return match ($entity) {
            'products' => [
                'sku' => [$presence, 'string', 'max:50', Rule::unique('products')->ignore($id)],
                'name' => [$presence, 'string', 'max:255'],
                'unit' => [$presence, 'string', 'max:30'],
                'retail_price' => [$presence, 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
            ],
            'departments' => array_merge([
                'code' => [$presence, 'string', 'max:30', Rule::unique('departments')->ignore($id)],
                'name' => [$presence, 'string', 'max:255'],
                'manager_name' => [$presence, 'string', 'max:255'],
                'phone' => [$presence, 'string', 'max:30'],
                'daily_plan' => [$presence, 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
            ], $creating ? [
                'initial_sale' => ['required', 'array:product_id,sale_date,quantity'],
                'initial_sale.product_id' => $sale['product_id'],
                'initial_sale.sale_date' => $sale['sale_date'],
                'initial_sale.quantity' => $sale['quantity'],
            ] : []),
            'sales' => array_map(fn ($rules) => array_merge([$presence], array_slice($rules, 1)), $sale) + [
                'department_id' => [$presence, 'integer', 'exists:departments,id'],
            ],
        };
    }
}
