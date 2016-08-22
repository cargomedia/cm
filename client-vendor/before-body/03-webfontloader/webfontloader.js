(function(d) {
  var config = document.documentElement.getAttribute('data-web-font-loader');
  if (config) {
    WebFontConfig = JSON.parse(config);
    var wf = d.createElement('script'), s = d.scripts[0];
    wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js';
    s.parentNode.insertBefore(wf, s);
  }
})(document);
