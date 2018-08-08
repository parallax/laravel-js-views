import Vue from 'vue'

export default function render(App, props) {
  new Vue({
    el: '#app',
    components: {
      App
    },
    template: '<app />'
  })
}
