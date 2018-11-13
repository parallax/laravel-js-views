import render from '__laravel_render_server__'

import(/* webpackChunkName: 'js/server/[request]' */ `__laravel_views__/${
  global.Laravel.___page
}.js`).then(component => {
  Promise.resolve(
    render(component.default || component, global.Laravel.___props)
  ).then(res => {
    print(JSON.stringify(res))
  })
})
