<?php

namespace Parallax\LaravelJsViews;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\Presets\Preset;

class ViewsPreset extends Preset
{
    /**
     * Install the preset.
     *
     * @return void
     */
    public static function install()
    {
        static::ensureComponentDirectoryExists();
        static::ensureLayoutDirectoryExists();
        static::updatePackages();
        static::updateScripts();
        static::updateWebpackConfiguration();
        static::updateWelcomeView();
        static::removeBootstrapping();
        static::removeComponent();
        static::removeNodeModules();
        static::removeBuilt();
        static::createLayout();
    }

    /**
     * Ensure the layout directory we need exists.
     *
     * @return void
     */
    protected static function ensureLayoutDirectoryExists()
    {
        $filesystem = new Filesystem;
        if (! $filesystem->isDirectory($directory = resource_path('views/layouts'))) {
            $filesystem->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Update the "package.json" file.
     *
     * @return void
     */
    protected static function updateScripts()
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages['scripts'] = array_merge(
            array_key_exists('scripts', $packages) ? $packages['scripts'] : [],
            ['build' => 'cross-env JS_ENV=web npm run prod && cross-env JS_ENV=node npm run prod']
        );

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Remove the example component.
     *
     * @return void
     */
    protected static function removeComponent()
    {
        (new Filesystem)->delete(
            resource_path('assets/js/components/ExampleComponent.vue')
        );
    }

    /**
     * Remove the bootstrapping files.
     *
     * @return void
     */
    protected static function removeBootstrapping()
    {
        tap(new Filesystem, function ($files) {
            $files->delete(resource_path('assets/js/app.js'));
            $files->delete(resource_path('assets/js/bootstrap.js'));
        });
    }

    /**
     * Remove the installed Node modules.
     *
     * @return void
     */
    protected static function removeNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));
            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Remove the built JS.
     *
     * @return void
     */
    protected static function removeBuilt()
    {
        (new Filesystem)->deleteDirectory(public_path('js'));
    }

    /**
     * Create the example layout.
     *
     * @return void
     */
    protected static function createLayout()
    {
        copy(__DIR__.'/templates/layout-stub.blade.php', resource_path('views/layouts/example.blade.php'));
    }
}
