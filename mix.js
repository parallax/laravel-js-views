let mix = require('laravel-mix')
let Manifest = require('laravel-mix/src/Manifest.js')
let webpack = require('webpack')
let ExtendsManifestPlugin = require('./src/js/extends-manifest-plugin.js')
let path = require('path')
let env = process.env.JS_ENV || 'web'

mix.extend(
  'views',
  new class {
    register(lib) {
      Mix.bundlingJavaScript = true

      if (env === 'node') {
        Mix.manifest = new Manifest('mix-manifest-node.json')
      }
    }

    dependencies() {
      return [
        'clean-webpack-plugin',
        'preact',
        'preact-render-to-string',
        'babel-preset-preact',
        'babel-plugin-syntax-dynamic-import'
      ]
    }

    webpackRules() {}

    webpackPlugins() {}

    webpackConfig(config) {
      dset(
        config,
        ['entry', `js/${env}/main`],
        path.resolve(__dirname, `./src/js/preact/entry.js`)
      )

      config.target = env

      dset(
        config,
        ['resolve', 'alias', '__laravel_views__'],
        path.resolve(config.context, './resources/views')
      )
      dset(
        config,
        ['resolve', 'alias', '__laravel_render_node__$'],
        path.resolve(__dirname, './src/js/preact/render-node.js')
      )
      dset(
        config,
        ['resolve', 'alias', '__laravel_render_web__$'],
        path.resolve(__dirname, './src/js/preact/render-web.js')
      )

      config.plugins.push(
        new webpack.DefinePlugin({
          'process.env.JS_ENV': JSON.stringify(env)
        })
      )

      if (env !== 'node') {
        let CleanWebpackPlugin = require('clean-webpack-plugin')
        config.plugins.push(
          new CleanWebpackPlugin(['public/js'], { root: config.context }),
          new ExtendsManifestPlugin()
        )
      }
    }

    webpackRules() {
      return [
        {
          test: /\.jsx?$/,
          exclude: /(node_modules|bower_components)/,
          use: [
            {
              loader: 'babel-loader',
              options: Config.babel()
            }
          ]
        }
      ]
    }

    babelConfig() {
      return {
        presets: ['babel-preset-preact'],
        plugins: ['babel-plugin-syntax-dynamic-import']
      }
    }
  }()
)

// https://github.com/lukeed/dset
function dset(obj, keys, val) {
  keys.split && (keys = keys.split('.'))
  var i = 0,
    l = keys.length,
    t = obj,
    x
  for (; i < l; ++i) {
    x = t[keys[i]]
    t = t[keys[i]] = i === l - 1 ? val : x == null ? {} : x
  }
}
