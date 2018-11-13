# laravel-js-views

## Getting started

Add the repo to your **`composer.json`** file:

```json
"repositories": [
  {
    "url": "https://github.com/parallax/laravel-js-views.git",
    "type": "git"
  }
]
```

```
$ composer require parallax/laravel-js-views
```

### Preset

If you’re starting a new Laravel project with Laravel Mix you can get set up quickly by using one of the `laravel-js-views` presets:

- `views:preact`
- `views:vue`

```bash
$ php artisan preset views:preact
```

### Manual

Add `laravel-js-views` to your Laravel Mix configuration:

```diff
let mix = require('laravel-mix')
+ require('./vendor/parallax/laravel-js-views')

+ mix.views()
```

Add a new npm script to `package.json` which compiles client and server versions of your views for production:

```
"build": "cross-env LARAVEL_ENV=client npm run prod && cross-env LARAVEL_ENV=server npm run prod"
```

## Layouts

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
    <script src="{{ mix('/js/main.js') }}"></script>
  </body>
</html>
```

## Views

Create your view components in the `/resources/views` directory, for example `/resources/views/home.js`

```js
import { h, Component } from 'preact'

let Home = () => <h1>Hello, world</h1>
export default Home
```

Render the view like you would any blade view:

```php
// /routes/web.php
Route::view('/', 'home');
```

## Passing data to a view

You can pass data to a view in the same way you pass data to a blade view:

```php
// /routes/web.php
Route::view('/', 'home', ['title' => 'Hello, world']);
```

The data is then passed into the `home.js` component as props:

```js
import { h } from 'preact'

let Home = ({ title }) => <h1>{title}</h1>
export default Home
```
