import render from '__laravel_render_client__'

import(/* webpackChunkName: 'js/[request]' */ `__laravel_views__/${
  window.Laravel.___page
}.js`).then(c => {
  render(c.default || c, window.Laravel.___props)
})

if (module.hot) {
  module.hot.accept()
  let NextApp = require(`__laravel_views__/${window.Laravel.___page}.js`)
  render(NextApp.default || NextApp, window.Laravel.___props)
}