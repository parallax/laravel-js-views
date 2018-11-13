<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;

class ReactViewsPreset extends ViewsPreset
{
    /**
     * Update the given package array.
     *
     * @param  array  $packages
     * @return array
     */
    protected static function updatePackageArray(array $packages)
    {
        return [
            'babel-preset-react' => '^6.24.1',
            'react' => '^16.6.3',
            'react-dom' => '^16.6.3',
            'babel-plugin-syntax-dynamic-import' => '^6.18.0',
            'clean-webpack-plugin' => '^0.1.19',
        ] + Arr::except($packages, ['vue']);
    }

    /**
     * Update the Webpack configuration.
     *
     * @return void
     */
    protected static function updateWebpackConfiguration()
    {
        copy(__DIR__.'/js/react/mix-stub.js', base_path('webpack.mix.js'));
    }

    /**
     * Create the example view.
     *
     * @return void
     */
    protected static function updateWelcomeView()
    {
        (new Filesystem)->delete(resource_path('views/welcome.blade.php'));
        copy(__DIR__.'/js/react/page-stub.js', resource_path('views/welcome.js'));
    }
}
