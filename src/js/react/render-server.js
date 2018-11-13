import React from 'react'
import ReactDOMServer from 'react-dom/server'

export default function render(App, props) {
  return {
    html: ReactDOMServer.renderToString(
      React.createElement('div', { id: 'app' }, [
        React.createElement(App, props)
      ])
    )
  }
}
