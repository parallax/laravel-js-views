import renderVueComponentToString from 'vue-server-renderer/basic.js'
import Vue from 'vue'
import App from '__laravel_app__'

export default function render(component, props) {
  Vue.use({
    install(v) {
      v.prototype.$laravel = props
    }
  })

  return new Promise((resolve, reject) => {
    renderVueComponentToString(
      new Vue({
        components: { App },
        data: { component },
        template: '<div id="app"><app :component="component" /></div>'
      }),
      (err, html) => {
        if (err) return reject(err)
        resolve({ html })
      }
    )
  })
}
