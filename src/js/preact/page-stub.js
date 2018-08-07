import { h, Component } from 'preact'

export default class Welcome extends Component {
  constructor(props) {
    super(props)
    this.extends = 'layouts.example'
  }
  render() {
    return <h1>Welcome</h1>
  }
}
