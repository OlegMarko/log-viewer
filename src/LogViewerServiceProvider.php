<?php

namespace Fixik\LogViewer;

use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'log-viewer');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor'),
        ], 'log-viewer-views');
        $this->publishes([
            __DIR__ . '/config/log-viewer.php' => config_path('log-viewer.php'),
        ], 'log-viewer-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/log-viewer.php',
            'log-viewer'
        );
    }
}
