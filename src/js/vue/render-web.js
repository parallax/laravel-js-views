import Vue from 'vue'

export default function render(App, props) {
  Vue.use({
    install(v) {
      v.prototype.$laravel = props
    }
  })

  new Vue({
    el: '#app',
    components: {
      App
    },
    template: '<div id="app"><app /></div>'
  })
}
