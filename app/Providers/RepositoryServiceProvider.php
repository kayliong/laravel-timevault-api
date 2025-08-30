<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\Objects\ObjectRepositoryInterface;
use App\Repositories\Objects\ObjectRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
         $this->app->bind(ObjectRepositoryInterface::class, ObjectRepository::class);
    }
}
