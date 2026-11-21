<?php
namespace App\Http\Controllers;
use App\Http\Requests\TradeRequest;
use App\Http\Resources\TradeResource;
use App\Interfaces\TradeRepositoryInterface;
use App\Models\{Department, Product, Sale};
use Illuminate\Support\Facades\DB;
class TradeController extends Controller
{
    public function __construct(private TradeRepositoryInterface $repository) {}
    public function index(string $entity) { return TradeResource::collection($this->repository->paginate($entity)); }
    public function show(int $id, string $entity) { return new TradeResource($this->repository->find($entity, $id)); }
    // Serializes mutations affecting the mandatory Department 1..* Sale invariant.
    // Every mutation acquires the same sentinel lock before accessing entity rows.
    private function transaction(callable $callback) {
        return DB::transaction(function () use ($callback) {
            DB::table('trade_locks')->where('id', 1)->lockForUpdate()->first();
            return $callback();
        }, 3);
    }
    private function priced(array $data): array {
        $data['unit_price'] = Product::findOrFail($data['product_id'])->retail_price;
        return $data;
    }
    public function store(TradeRequest $request, string $entity) {
        $model = $this->transaction(function () use ($request, $entity) {
            $data = $request->validated();
            if ($entity === 'departments') {
                $sale = $data['initial_sale']; unset($data['initial_sale']);
                $department = $this->repository->create($entity, $data);
                $this->repository->create('sales', $this->priced($sale + ['department_id' => $department->id]));
                return $department;
            }
            return $this->repository->create($entity, $entity === 'sales' ? $this->priced($data) : $data);
        });
        return (new TradeResource($model))->response()->setStatusCode(201);
    }
    public function update(TradeRequest $request, int $id, string $entity) {
        $model = $this->transaction(function () use ($request, $entity, $id) {
            $model = $this->repository->find($entity, $id);
            $data = $request->validated();
            if ($entity === 'sales') {
                if (isset($data['department_id']) && (int) $data['department_id'] !== $model->department_id) {
                    abort_if($model->department->sales()->count() <= 1, 409, 'Нельзя переносить последнюю продажу отдела.');
                }
                if (isset($data['product_id']) && (int) $data['product_id'] !== $model->product_id) {
                    $data = $this->priced($data);
                }
            }
            return $this->repository->update($model, $data);
        });
        return new TradeResource($model);
    }
    public function destroy(int $id, string $entity) {
        $this->transaction(function () use ($entity, $id) {
            $model = $this->repository->find($entity, $id);
            if ($entity === 'sales') {
                abort_if($model->department->sales()->count() <= 1, 409, 'Нельзя удалить последнюю продажу отдела.');
            } else {
                abort_if($model->sales()->exists(), 409, 'Объект имеет продажи; удаление запрещено.');
            }
            $this->repository->delete($model);
        });
        return response()->noContent();
    }
}
