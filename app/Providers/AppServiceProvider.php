<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Domain;
use App\Models\Domain as DomainModel;
use App\Services\FineUploader;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        URL::forceScheme(env('APP_SCHEME'));

        if ((App::runningInConsole() || !App::getDomain()) && Schema::hasTable('domains')) { // Cron or PHP CLI
            $domains = DomainModel::all()->keyBy('domain');
            foreach ($domains as $key => $value) {
                Lang::addNamespace($key, resource_path() . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . 'lang');
            }
        }

        /*DB::listen(function ($query) {
            Log::debug($query->sql);
            Log::debug($query->bindings);
        });*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('domain', function () {
            return new Domain();
        });

        $this->app->singleton('fineuploader', function () {
            return new FineUploader();
        });
    }
}
