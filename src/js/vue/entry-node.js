import render from '__laravel_render_node__'

import(/* webpackChunkName: 'js/node/[request]' */ `__laravel_views__/${
  global.page
}.vue`).then(c => {
  let Component = c.default || c
  Promise.resolve(render(Component, global.props)).then(res => {
    print(JSON.stringify(res))
  })
})
