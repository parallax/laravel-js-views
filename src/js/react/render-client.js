import React from 'react'
import ReactDOM from 'react-dom'

export default function render(App, props) {
  return ReactDOM.render(
    React.createElement('div', { id: 'app' }, [
      React.createElement(App, props)
    ]),
    document.getElementById('app')
  )
}
