import render from '__laravel_render_node__'

import(/* webpackChunkName: 'js/node/pages/[request]' */ `__laravel_views__/${
  global.page
}.vue`).then(c => {
  let Component = c.default
  render(Component, global.props).then(res => {
    print(JSON.stringify(res))
  })
})
