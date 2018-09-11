if (process.env.LARAVEL_ENV === 'server') {
  require('./entry-server')
} else {
  require('./entry-client')
}
