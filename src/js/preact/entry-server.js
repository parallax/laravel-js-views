import { h } from 'preact'
import render from '__laravel_render_server__'

import(/* webpackChunkName: 'js/server/[request]' */ `__laravel_views__/${
  global.__INITIAL_LARAVEL_PAGE__
}.js`).then(c => {
  let Component = c.default
  Promise.resolve(render(Component, global.__INITIAL_LARAVEL_PROPS__)).then(
    res => {
      print(JSON.stringify(res))
    }
  )
})
