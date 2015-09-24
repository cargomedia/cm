/*
 * Author: CM
 * Custom CM rules for Modernizr.
 */

(function() {
  Modernizr.addTest('inputinfixed', function() {
    return !navigator.userAgent.match(/(iPad|iPhone|iPod)/i);
  });
  Modernizr.addTest('contenteditable-plaintext', function() {
    var div = document.createElement('div');
    div.setAttribute('contenteditable', 'plaintext-only');
    return div.contentEditable === 'plaintext-only';
  });
})();
