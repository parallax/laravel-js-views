<?php

namespace Parallax\LaravelJsViews;

use Closure;
use Illuminate\Http\Response;

class LaravelJsViewsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldHandle($response)) {
            $html = $response->getContent();
            list($html, $scripts) = $this->extractScripts($html);

            if ($scripts !== '') {
                $response->setContent(
                    preg_replace('/(<head(>|\s[^>]*>))/i', '$0' . $scripts, $html)
                );
            }
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

    protected function isAResponseObject($response)
    {
        return is_object($response) && $response instanceof Response;
    }

    protected function isAnHtmlResponse(Response $response)
    {
        $type = $response->headers->get('Content-Type');
        return strtolower(strtok($type, ';')) === 'text/html';
    }

    protected function shouldHandle($response)
    {
        return $this->isAResponseObject($response) && $this->isAnHtmlResponse($response);
    }
}
