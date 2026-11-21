<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Sale extends Model
{
    protected $fillable = ['department_id', 'product_id', 'sale_date', 'quantity', 'unit_price'];
    protected function casts(): array { return ['sale_date' => 'date:Y-m-d', 'quantity' => 'decimal:3', 'unit_price' => 'decimal:2']; }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
