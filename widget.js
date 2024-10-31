(function (w, d, s, o, f, js, fjs) {
  w['qokka-proof-widget'] = o
  w[o] = w[o] || function () { (w[o].q = w[o].q || []).push(arguments) }
  js = d.createElement(s), fjs = d.getElementsByTagName(s)[0]
  js.id = o
  js.src = f
  js.async = 1
  fjs.parentNode.insertBefore(js, fjs)
}(window, document, 'script', 'qpw', 'https://cdn.usecredible.com/lib/widget.js'))
qpw({ apiKey: credible_settings.apiKey })
console.log('Credible v0.1.2: widget loaded.')
