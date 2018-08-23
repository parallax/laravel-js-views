import { h } from 'preact'
import render from '__laravel_render_node__'

import(/* webpackChunkName: 'js/node/[request]' */ `__laravel_views__/${
  global.page
}.js`).then(c => {
  let Component = c.default
  print(JSON.stringify(render(Component, global.props)))
})
