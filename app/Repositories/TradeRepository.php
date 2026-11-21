<?php
namespace App\Repositories;
use App\Interfaces\TradeRepositoryInterface;
use App\Models\{Department, Product, Sale};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class TradeRepository implements TradeRepositoryInterface
{
    private function model(string $entity): string {
        return match ($entity) { 'departments' => Department::class, 'products' => Product::class, 'sales' => Sale::class };
    }
    public function paginate(string $entity): LengthAwarePaginator { return $this->model($entity)::orderBy('id')->paginate(25); }
    public function find(string $entity, int $id): Model { return $this->model($entity)::findOrFail($id); }
    public function create(string $entity, array $data): Model { return $this->model($entity)::create($data); }
    public function update(Model $model, array $data): Model { $model->update($data); return $model->refresh(); }
    public function delete(Model $model): void { $model->delete(); }
}
