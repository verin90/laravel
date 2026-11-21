<?php
namespace App\Interfaces;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface TradeRepositoryInterface
{
    public function paginate(string $entity): LengthAwarePaginator;
    public function find(string $entity, int $id): Model;
    public function create(string $entity, array $data): Model;
    public function update(Model $model, array $data): Model;
    public function delete(Model $model): void;
}
