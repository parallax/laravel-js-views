<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
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

        View::creator('*', function($view) {
            $viewPath = $view->getPath();

            $ext = pathinfo($viewPath, PATHINFO_EXTENSION);
            if ($ext !== 'js' && $ext !== 'vue') {
                return;
            }

            $viewDir = resource_path('views');
            $name = str_replace($viewDir . '/', '', $viewPath);
            $name = preg_replace('/\.(js|vue)$/', '', $name);

            $viewFactory = $view->getFactory();
            $sharedData = (array) $viewFactory->getShared();
            $data = $view->getData();
            $props = array_merge($sharedData, $data);

            if (request()->ajax()) {
                $view->setPath(__DIR__ . '/templates/json.blade.php');
                $view->with(
                    'data',
                    json_encode([
                        'view' => $name,
                        'props' => $props
                    ])
                );
                return;
            }

            $routes = [];
            foreach (app()->routes->getRoutes() as $route) {
                $routeName = $route->getName();
                if ($routeName !== null) {
                    $uri = $route->uri;
                    $routes[$routeName] = ($uri === '/' ? '' : '/') . $route->uri;
                }
            }

            $sections = [];
            $scripts = '<!-- __laravel_js_views_scripts_start__ --><script>window.routes=' . json_encode($routes) . ';window.page="' . $name . '";window.__INITIAL_PROPS__=' . json_encode($props) . '</script><!-- __laravel_js_views_scripts_end__ -->';

            if (class_exists('V8Js') && file_exists(public_path('js/node/main.js'))) {
                $bootstrap = 'var console=["log","warn","error","info","assert","clear","count","countReset","debug","dir","dirxml","exception","group","groupCollapsed","groupEnd","profile","profileEnd","table","time","timeEnd","timeLog","timeStamp","trace"].reduce((acc,curr) => {acc[curr]=(...args)=>{require(`__laravel_console_${curr}_${JSON.stringify(args)}__`)};return acc;}, {});';
                $bootstrap .= 'var process = { env: { VUE_ENV: "server", NODE_ENV: "production" } };';
                $bootstrap .= 'this.global = { process, page: "' . $name . '", routes: ' . json_encode($routes) . ', props: ' . json_encode($props) . ' };';

                $v8 = new \V8Js();
                $v8->setModuleLoader(function($path) use ($bootstrap) {
                    preg_match('/^__laravel_console_(log|warn|error|info|assert|clear|count|countReset|debug|dir|dirxml|exception|group|groupCollapsed|groupEnd|profile|profileEnd|table|time|timeEnd|timeLog|timeStamp|trace)_(.*?)__$/', $path, $matches);
                    if (count($matches) > 0) {
                        $type = $matches[1];
                        $args = json_decode($matches[2]);
                        array_map(function($arg) use ($type) {
                            switch ($type) {
                                case 'log':
                                    Log::debug($arg);
                                    break;
                                case 'warn':
                                    Log::warning($arg);
                                    break;
                                case 'error':
                                    Log::error($arg);
                                    break;
                                case 'info':
                                    Log::info($arg);
                                    break;
                            }
                        }, $args);
                        return 'module.exports=undefined;';
                    }
                    return $bootstrap . file_get_contents(public_path($path));
                });
                $js = $bootstrap . file_get_contents(public_path('js/node/main.js'));
                ob_start();
                $v8->executeString($js);

                $sections = array_merge(json_decode(ob_get_clean(), true), $sections);
            } else {
                $sections['html'] = config('js-views.fallback', '<div id="app"></div>');
            }

            // TODO: check there’s a `html` section defined
            $sections['html'] .= $scripts;

            $layoutManifest = json_decode(file_get_contents(public_path('layout-manifest.json')), true);
            $view->setPath(View::getFinder()->find($layoutManifest[$name]));

            foreach ($sections as $section => $value) {
                $viewFactory->startSection($section);
                echo $value;
                $viewFactory->stopSection();
            }
        });
    }
}
