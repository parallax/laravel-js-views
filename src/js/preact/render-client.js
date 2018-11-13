import { h, render as r } from 'preact'
import App from '__laravel_app__'

export default function render(Component, props) {
  return r(
    <div id="app">
      <App component={Component} props={props} />
    </div>,
    document.body,
    document.getElementById('app')
  )
}
