<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Masjid\MasjidRepository;
use App\Repositories\Masjid\MasjidRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            MasjidRepositoryInterface::class,
            MasjidRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
