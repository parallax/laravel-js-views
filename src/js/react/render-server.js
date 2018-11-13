import React from 'react'
import ReactDOMServer from 'react-dom/server'
import App from '__laravel_app__'

export default function render(Component, props) {
  return {
    html: ReactDOMServer.renderToString(
      <div id="app">
        <App component={Component} props={props} />
      </div>
    )
  }
}
