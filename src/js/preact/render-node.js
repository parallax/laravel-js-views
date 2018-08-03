import { h } from 'preact'
import renderToString from 'preact-render-to-string'

export default function render(App, props) {
  return {
    html: renderToString(h('div', { id: 'app' }, [h(App, props)]))
  }
}
