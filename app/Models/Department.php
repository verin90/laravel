<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Department extends Model
{
    protected $fillable = ['code', 'name', 'manager_name', 'phone', 'daily_plan'];
    protected function casts(): array { return ['daily_plan' => 'decimal:2']; }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
}
