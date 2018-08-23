let path = require('path')
let fs = require('fs')

function ExtendsManifestPlugin() {}

ExtendsManifestPlugin.prototype.apply = function(compiler) {
  let manifest = {}

  compiler.plugin('compilation', function(compilation, params) {
    manifest = {}
    let viewsDir = path.resolve(process.cwd(), 'resources', 'views')

    compilation.plugin('build-module', function(module) {
      if (module.resource && module.resource.endsWith('.vue')) {
        module.loaders.push(path.resolve(__dirname, 'vue-extends-loader.js'))
      }
    })

    compilation.plugin('succeed-module', function(module) {
      if (module.resource && module.resource.startsWith(viewsDir)) {
        let source = module.originalSource().source()
        let matches

        if (module.resource.endsWith('.vue')) {
          matches = source.match(/\/\* __laravel_extends__\[([^\]]+)\] \*\//)
        } else {
          matches = source.match(
            /\.extends = ((?=["'])(?:"[^"\\]*(?:\\[\s\S][^"\\]*)*"|'[^'\\]*(?:\\[\s\S][^'\\]*)*'))/
          )
        }

        if (matches) {
          manifest[
            module.resource
              .replace(viewsDir + path.sep, '')
              .replace(/\.(js|vue)$/, '')
          ] = matches[1].substr(1, matches[1].length - 2)
        }
      }
    })
  })

  compiler.plugin('emit', function(compilation, callback) {
    let filename = path.join(
      path.relative(process.cwd(), 'public'),
      'layout-manifest.json'
    )

    let prevManifest = {}
    try {
      prevManifest = JSON.parse(fs.readFileSync(filename, 'utf-8'))
    } catch (err) {}

    let nextManifest = JSON.stringify({ ...prevManifest, ...manifest }, null, 2)

    fs.writeFileSync(filename, nextManifest, 'utf-8')

    callback()
  })
}

module.exports = ExtendsManifestPlugin
