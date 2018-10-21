<?php

namespace Parallax\LaravelJsViews;

class Renderer
{
    private $js;
    private $vars;
    private $console = [
        'log' => 'debug',
        'warn' => 'warning',
        'error' => 'error',
        'info' => 'info'
    ];
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

        $js = $this->bootstrap . $this->js;

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
            if (isset($this->console[$type])) {
                array_map(
                    ['Illuminate\Support\Facades\Log', $this->console[$type]],
                    array_map([$this, 'jsonEncodePretty'], $args)
                );
            }
            return 'module.exports=undefined;';
        }

        return $this->bootstrap . file_get_contents(public_path($path));
    }

    private function jsonEncodePretty($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
