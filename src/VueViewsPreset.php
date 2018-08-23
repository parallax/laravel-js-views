<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;

class PreactViewsPreset extends ViewsPreset
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
            'vue' => '^2.5.17',
            'vue-server-renderer' => '^2.5.17',
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
        copy(__DIR__.'/js/vue/mix-stub.js', base_path('webpack.mix.js'));
    }

    /**
     * Create the example view.
     *
     * @return void
     */
    protected static function updateWelcomeView()
    {
        (new Filesystem)->delete(resource_path('views/welcome.blade.php'));
        copy(__DIR__.'/js/vue/page-stub.vue', resource_path('views/welcome.vue'));
    }
}
