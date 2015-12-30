<?php

namespace AldoZumaran\Uploader;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/uploader.php' => config_path('uploader.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('Uploader', function($app)
        {
            return new Uploader($app['request']);
        });
    }
}
