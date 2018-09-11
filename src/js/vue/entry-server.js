import render from '__laravel_render_server__'

import(/* webpackChunkName: 'js/server/[request]' */ `__laravel_views__/${
  global.__INITIAL_LARAVEL_PAGE__
}.vue`).then(component => {
  Promise.resolve(
    render(component.default || component, global.__INITIAL_LARAVEL_PROPS__)
  ).then(res => {
    print(JSON.stringify(res))
  })
})
