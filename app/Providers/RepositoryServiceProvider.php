<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\TradeRepositoryInterface;
use App\Repositories\TradeRepository;
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void { $this->app->bind(TradeRepositoryInterface::class, TradeRepository::class); }
}
