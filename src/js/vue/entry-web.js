import render from '__laravel_render_web__'

function getCurrentUrl() {
  return `${window.location.pathname || ''}${window.location.search || ''}`
}

let loaded = {
  [getCurrentUrl()]: import(/* webpackChunkName: 'js/web/pages/[request]' */ `__laravel_views__/${
    window.page
  }.vue`).then(c => {
    return {
      view: window.page,
      Component: c.default,
      data: window.__INITIAL_PROPS__
    }
  })
}

function main({ Component, data, view }) {
  render(Component, data)
}

loaded[getCurrentUrl()].then(main)

window.addEventListener('click', e => {
  let link = e.target.closest('a')
  if (!link) return
  e.preventDefault()
  let path = link.getAttribute('href')
  routeTo(path)
})

window.addEventListener('popstate', () => {
  routeTo(getCurrentUrl(), false)
})

// window.addEventListener('mouseover', e => {
//   let link = e.target.closest('a')
//   if (!link) return
//   load(link.getAttribute('href'))
// })

function routeTo(path, push = true) {
  load(path).then(stuff => {
    push && history.pushState({}, '', path)
    main(stuff)
  })
}

function load(path) {
  if (!loaded[path]) {
    let v
    let d
    loaded[path] = window
      .fetch(path, { headers: { 'x-requested-with': 'XMLHttpRequest' } })
      .then(res => res.json())
      .then(json => {
        v = json.view
        d = json.props

        return import(/* webpackChunkName: 'js/web/pages/[request]' */ `__laravel_views__/${
          json.view
        }.vue`)
      })
      .then(c => {
        return {
          Component: c.default,
          view: v,
          data: d
        }
      })
  }
  return loaded[path]
}

if (module.hot) {
  module.hot.accept()
  let NextApp = require(`__laravel_views__/${view}.js`).default
  render(NextApp, data)
}
