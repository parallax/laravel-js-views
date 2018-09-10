<?php

namespace Parallax\LaravelJsViews;

use Closure;

class LaravelJsViewsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $html = $response->getContent();
        list($html, $scripts) = $this->extractScripts($html);

        if ($scripts !== '') {
            $response->setContent(
                str_replace('<head>', '<head>' . $scripts, $html)
            );
        }

        return $response;
    }

    private function extractScripts($html)
    {
        $scripts = '';
        $html = preg_replace_callback(
            '/<laravel-js-views-scripts>(.*?)<\/laravel-js-views-scripts>/s',
            function ($matches) use (&$scripts) {
                $scripts = $matches[1];
                return '';
            },
            $html
        );

        return [$html, $scripts];
    }
}
