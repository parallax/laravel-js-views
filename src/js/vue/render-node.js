import renderVueComponentToString from 'vue-server-renderer/basic.js'
import Vue from 'vue'

export default function render(App, props) {
  return new Promise(resolve => {
    renderVueComponentToString(
      new Vue({ components: { App }, template: '<app />' }),
      (err, html) => {
        resolve({ html })
      }
    )
  })
}
