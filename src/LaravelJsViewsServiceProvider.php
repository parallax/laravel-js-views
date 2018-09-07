<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Console\PresetCommand;

class LaravelJsViewsServiceProvider extends ServiceProvider
{
    /**
     *
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/js-views.php' => config_path('js-views.php')
        ]);

        $this->app['router']->pushMiddlewareToGroup('web', LaravelJsViewsMiddleware::class);

        PresetCommand::macro('views:preact', function($command) {
            PreactViewsPreset::install();

            $command->info('laravel-js-views (Preact) scaffolding installed successfully.');
            $command->comment('Please run "npm install && npm run build" to compile your fresh scaffolding.');
        });

        PresetCommand::macro('views:vue', function($command) {
            VueViewsPreset::install();

            $command->info('laravel-js-views (Vue) scaffolding installed successfully.');
            $command->comment('Please run "npm install && npm run build" to compile your fresh scaffolding.');
        });

        View::addExtension('js', 'blade');
        View::addExtension('vue', 'blade');

        View::creator('*', JsCreator::class);
    }
}
