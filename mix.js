let mix = require('laravel-mix')
let Manifest = require('laravel-mix/src/Manifest.js')
let Vue = require('laravel-mix/src/components/Vue.js')
let Preact = require('laravel-mix/src/components/Preact.js')
let webpack = require('webpack')
let ExtendsManifestPlugin = require('./src/js/extends-manifest-plugin.js')
let path = require('path')
let env = process.env.JS_ENV || 'web'

let deps = {
  preact: ['preact-render-to-string'],
  vue: ['vue-server-renderer']
}

mix.extend(
  'views',
  new class {
    register(lib = 'preact') {
      Mix.bundlingJavaScript = true

      if (env === 'node') {
        Mix.manifest = new Manifest('mix-manifest-node.json')
      }

      this.lib = lib.toLowerCase()

      if (this.lib === 'vue') {
        this.super = new Vue()
      } else if (this.lib === 'preact') {
        this.super = new Preact()
      }
    }

    dependencies() {
      return ['clean-webpack-plugin', 'babel-plugin-syntax-dynamic-import']
        .concat(deps[this.lib])
        .concat(this.super.dependencies())
    }

    webpackPlugins() {
      return this.super.webpackPlugins ? this.super.webpackPlugins() : []
    }

    webpackConfig(config) {
      this.super.webpackConfig && this.super.webpackConfig(config)

      dset(
        config,
        ['entry', env === 'node' ? 'js/node/main' : 'js/main'],
        path.resolve(__dirname, `./src/js/${this.lib}/entry.js`)
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
        path.resolve(__dirname, `./src/js/${this.lib}/render-node.js`)
      )
      dset(
        config,
        ['resolve', 'alias', '__laravel_render_web__$'],
        path.resolve(__dirname, `./src/js/${this.lib}/render-web.js`)
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
      return this.super.webpackRules ? this.super.webpackRules() : []
    }

    babelConfig() {
      let config = this.super.babelConfig ? this.super.babelConfig() : {}
      config.plugins = config.plugins
        ? [...config.plugins, 'babel-plugin-syntax-dynamic-import']
        : ['babel-plugin-syntax-dynamic-import']
      return config
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
