import renderVueComponentToString from 'vue-server-renderer/basic.js'
import Vue from 'vue'

export default function render(App, props) {
  Vue.use({
    install(v) {
      v.prototype.$laravel = props
    }
  })

  return new Promise((resolve, reject) => {
    renderVueComponentToString(
      new Vue({ components: { App }, template: '<div id="app"><app /></div>' }),
      (err, html) => {
        if (err) return reject(err)
        resolve({ html })
      }
    )
  })
}
