import { h } from 'preact'
import renderToString from 'preact-render-to-string'
import App from '__laravel_app__'

export default function render(Component, props) {
  return {
    html: renderToString(
      <div id="app">
        <App component={Component} props={props} />
      </div>
    )
  }
}
