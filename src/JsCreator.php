<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class JsCreator
{
    private $request;
    private $view;
    private $viewPath;
    private $viewDir;
    private $viewName;
    private $viewProps;
    private $viewFactory;

    /**
     * Create a new JS composer.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     *
     * @param  View  $view
     * @return void
     */
    public function create(\Illuminate\View\View $view)
    {
        $this->view = $view;
        $this->viewPath = $view->getPath();

        if (! $this->shouldHandle()) return;

        $this->viewDir = resource_path('views');
        $this->viewName = str_replace($this->viewDir . '/', '', $this->viewPath);
        $this->viewName = preg_replace('/\.(js|vue)$/', '', $this->viewName);

        $this->viewFactory = $this->view->getFactory();
        $this->viewProps = array_merge(
            (array) $this->viewFactory->getShared(),
            $this->view->getData()
        );

        if ($this->request->ajax()) {
            $this->createJsonResponse();
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
        $scripts = '<!-- __laravel_js_views_scripts_start__ --><script>window.routes=' . json_encode($routes) . ';window.page="' . $this->viewName . '";window.__INITIAL_PROPS__=' . json_encode($this->viewProps) . '</script><!-- __laravel_js_views_scripts_end__ -->';

        if (class_exists('V8Js') && file_exists(public_path('js/node/main.js'))) {
            $bootstrap = 'var console=["log","warn","error","info","assert","clear","count","countReset","debug","dir","dirxml","exception","group","groupCollapsed","groupEnd","profile","profileEnd","table","time","timeEnd","timeLog","timeStamp","trace"].reduce((acc,curr) => {acc[curr]=(...args)=>{require(`__laravel_console_${curr}_${JSON.stringify(args)}__`)};return acc;}, {});';
            $bootstrap .= 'var process = { env: { VUE_ENV: "server", NODE_ENV: "production" } };';
            $bootstrap .= 'this.global = { process, page: "' . $this->viewName . '", routes: ' . json_encode($routes) . ', props: ' . json_encode($this->viewProps) . ' };';

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
        $this->view->setPath(View::getFinder()->find($layoutManifest[$this->viewName]));

        foreach ($sections as $section => $value) {
            $this->viewFactory->startSection($section);
            echo $value;
            $this->viewFactory->stopSection();
        }
    }

    private function shouldHandle()
    {
        $ext = pathinfo($this->viewPath, PATHINFO_EXTENSION);
        return $ext === 'js' || $ext === 'vue';
    }

    private function createJsonResponse()
    {
        $this->view->setPath(__DIR__ . '/templates/json.blade.php');
        $this->view->with(
            'data',
            json_encode([
                'view' => $this->viewName,
                'props' => $this->viewProps
            ])
        );
    }
}
