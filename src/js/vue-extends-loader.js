module.exports = function(source) {
  let layout

  let result = source.replace(
    /<template([^>]*)extends=((?=["'])(?:"[^"\\]*(?:\\[\s\S][^"\\]*)*"|'[^'\\]*(?:\\[\s\S][^'\\]*)*'))([^>]*)>/,
    (m, p1, p2, p3) => {
      layout = p2
      return `<template${p1}${p3}>`
    }
  )

  if (layout) {
    let added = false
    result = result.replace(/<\/script>/, () => {
      added = true
      return `/* __laravel_extends__[${layout}] */</script>`
    })
    if (!added) {
      result = result + `<script>/* __laravel_extends__[${layout}] */</script>`
    }
  }

  return result
}
