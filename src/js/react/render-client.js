import React from 'react'
import ReactDOM from 'react-dom'
import App from '__laravel_app__'

export default function render(Component, props) {
  return ReactDOM.render(
    <App component={Component} props={props} />,
    document.getElementById('app')
  )
}
