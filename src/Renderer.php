<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\Facades\Log;

class Renderer
{
    private $js;
    private $vars;
    private $bootstrap = <<<'JS'
        var console = new Proxy({}, {
            get(_, prop) {
                return (...args) => {
                    require(`__laravel_console_${prop}_${JSON.stringify(args)}__`)
                }
            }
        });
JS;

    public function __construct($js, $vars = [])
    {
        $this->js = $js;
        $this->vars = $vars;

        foreach ($vars as $name => $value) {
            $this->bootstrap .= (strpos($name, '.') === false && strpos($name, '[') === false ? 'var ' : '') . $name . '=';
            $this->bootstrap .= json_encode($value) . ';';
        }
    }

    public function render()
    {
        $v8 = new \V8Js();
        $v8->setModuleLoader(function($path) {
            return $this->moduleLoader($path);
        });

        $js = $this->bootstrap . file_get_contents(public_path('js/node/main.js'));

        ob_start();
        $v8->executeString($js);

        return ob_get_clean();
    }

    private function moduleLoader($path)
    {
        preg_match('/^__laravel_console_([a-z]+)_(.*?)__$/i', $path, $matches);

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

        return $this->bootstrap . file_get_contents(public_path($path));
    }
}
