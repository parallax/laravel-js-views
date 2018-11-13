let mix = require('laravel-mix')
let Manifest = require('laravel-mix/src/Manifest.js')
let Vue = require('laravel-mix/src/components/Vue.js')
let Preact = require('laravel-mix/src/components/Preact.js')
let React = require('laravel-mix/src/components/React.js')
let webpack = require('webpack')
let path = require('path')
let env = process.env.LARAVEL_ENV || 'client'

let deps = {
  preact: ['preact-render-to-string'],
  vue: ['vue-server-renderer'],
  react: []
}

mix.extend(
  'views',
  new class {
    register(lib = 'preact') {
      if (env === 'server') {
        Mix.manifest = new Manifest('mix-manifest-server.json')
      }

      this.lib = lib.toLowerCase()

      if (this.lib === 'vue') {
        this.super = new Vue()
      } else if (this.lib === 'preact') {
        this.super = new Preact()
      } else if (this.lib === 'react') {
        this.super = new React()
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
        ['entry', env === 'server' ? 'js/server/main' : 'js/main'],
        path.resolve(__dirname, `./src/js/${this.lib}/entry.js`)
      )

      config.target = env === 'server' ? 'node' : 'web'

      dset(
        config,
        ['resolve', 'alias', '__laravel_views__'],
        path.resolve(config.context, './resources/views')
      )
      dset(
        config,
        ['resolve', 'alias', '__laravel_render_server__$'],
        path.resolve(__dirname, `./src/js/${this.lib}/render-server.js`)
      )
      dset(
        config,
        ['resolve', 'alias', '__laravel_render_client__$'],
        path.resolve(__dirname, `./src/js/${this.lib}/render-client.js`)
      )

      config.plugins.push(
        new webpack.DefinePlugin({
          'process.env.LARAVEL_ENV': JSON.stringify(env)
        })
      )

      if (env !== 'server') {
        let CleanWebpackPlugin = require('clean-webpack-plugin')
        config.plugins.push(
          new CleanWebpackPlugin(['public/js'], { root: config.context })
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
