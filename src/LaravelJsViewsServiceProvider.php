<?php

namespace Parallax\LaravelJsViews;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class LaravelJsViewsServiceProvider extends ServiceProvider
{
    /**
     *
     *
     * @return void
     */
    public function boot()
    {
        View::addExtension('js', 'blade');

        View::creator('*', function($view) {
            $viewPath = $view->getPath();

            if (pathinfo($viewPath, PATHINFO_EXTENSION) !== 'js') {
                return;
            }

            $viewDir = base_path('resources/views');
            $name = str_replace($viewDir . '/', '', $viewPath);
            $name = preg_replace('/\.js$/', '', $name);

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

            $output = [];
            $scripts = '<script>window.routes=' . json_encode($routes) . ';window.page="' . $name . '";window.__INITIAL_PROPS__=' . json_encode($props) . '</script>';

            if (file_exists(base_path('public/js/node/main.js'))) {
                $bootstrap = 'var console=["log","warn","error","info","assert","clear","count","countReset","debug","dir","dirxml","exception","group","groupCollapsed","groupEnd","profile","profileEnd","table","time","timeEnd","timeLog","timeStamp","trace"].reduce((acc,curr) => {acc[curr]=(...args)=>{require(`__laravel_console_${curr}_${JSON.stringify(args)}__`)};return acc;}, {});';
                $bootstrap .= 'var global={page:"' . $name . '",routes:' . json_encode($routes) . ',props:' . json_encode($props) . '};';

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
                    return $bootstrap . file_get_contents(base_path('public/' . $path));
                });
                $js = $bootstrap . file_get_contents(base_path('public/js/node/main.js'));
                ob_start();
                $v8->executeString($js);

                $output = json_decode(ob_get_clean(), true);

                $scripts .= '<script src="/js/web/main.js"></script>';
            } else {
                $scripts .= '<script src="http://localhost:8080/js/web/main.js"></script>';
            }

            $layoutManifest = json_decode(file_get_contents(base_path('public/layout-manifest.json')), true);
            $view->setPath(View::getFinder()->find($layoutManifest[$name]));

            foreach ($output as $section => $value) {
                $viewFactory->startSection($section);
                echo $value;
                $viewFactory->stopSection();
            }

            $viewFactory->startSection('scripts');
            echo $scripts;
            $viewFactory->stopSection();
        });
    }
}
