if (process.env.JS_ENV === 'node') {
  require('./entry-node')
} else {
  require('./entry-web')
}
