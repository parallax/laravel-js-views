import { h, render as r } from 'preact'

export default function render(App, props) {
  return r(
    h('div', { id: 'app' }, [h(App, props)]),
    document.body,
    document.getElementById('app')
  )
}
