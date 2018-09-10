<?php

namespace Parallax\LaravelJsViews;

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
        $this->viewDir = resource_path('views');
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

        $sections = [];
        $scripts = <<<HTML
            <laravel-js-views-scripts>
                <script>window.page='$this->viewName';window.__INITIAL_PROPS__={$this->json_encode($this->viewProps)}</script>
            </laravel-js-views-scripts>
HTML;

        try {
            $process = [
                'env' => [
                    'VUE_ENV' => 'server',
                    'NODE_ENV' => 'production'
                ]
            ];
            $renderer = new Renderer(
                file_get_contents(public_path('js/node/main.js')),
                [
                    'process' => $process,
                    'this.global' => [
                        'process' => $process,
                        'page' => $this->viewName,
                        'props' => $this->viewProps
                    ]
                ]
            );

            $sections = json_decode($renderer->render(), true);
        } catch (\Throwable $e) {
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

    private function json_encode($x)
    {
        return json_encode($x);
    }
}
