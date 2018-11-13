import render from '__laravel_render_client__'

import(/* webpackChunkName: 'js/[request]' */ `__laravel_views__/${
  window.Laravel.__page
}.js`).then(c => {
  render(c.default || c, window.Laravel)
})

if (module.hot) {
  module.hot.accept()
  let NextApp = require(`__laravel_views__/${window.Laravel.__page}.js`)
  render(NextApp.default || NextApp, window.Laravel)
}
