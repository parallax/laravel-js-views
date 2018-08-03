# laravel-js-views

## Getting started

```
composer require parallax/laravel-js-views
```

Add `laravel-js-views` to your Laravel Mix configuration:

```diff
let mix = require('laravel-mix')
+ require('./vendor/parallax/laravel-js-views/mix')

+ mix.views()
```

Add a new npm script to `package.json` which compiles web and node versions of your views for production:

```
"build": "cross-env JS_ENV=web npm run prod && cross-env JS_ENV=node npm run prod"
```

Create a blade layout in your `/resources/views` directory, for example `/resources/views/layouts/default.blade.php`:

```html
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Example</title>
</head>
<body>
  @yield('html')
  @yield('scripts')
</body>
</html>
```

Create your pages in the `/resources/views/pages` directory, for example `/resources/views/pages/home.js`

```js
import { h, Component } from 'preact'

export default class Home extends Component {
  constructor(props) {
    super(props)
    this.extends = 'layouts.default'
  }
  render() {
    return <h1>Hello, world</h1>
  }
}
```

Render the view like you would any blade view:

```php
// /routes/web.php
Route::view('/', 'pages.home');
```

## Passing data to a view

You can pass data to a view in the same way you pass data to a blade view:

```php
// /routes/web.php
Route::view('/', 'pages.home', ['title' => 'Hello, world']);
```

The data is then passed into the `pages/home.js` component as props:

```js
import { h } from 'preact'

let Home = ({ title }) => <h1>{title}</h1>
Home.extends = 'layouts.default'
export default Home
```
