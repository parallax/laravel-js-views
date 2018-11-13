import Vue from 'vue'
import App from '__laravel_app__'

export default function render(component, props) {
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
    data: {
      component
    },
    template: '<div id="app"><app :component="component" /></div>'
  })
}
