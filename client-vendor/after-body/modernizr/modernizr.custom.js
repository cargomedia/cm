/*
 * Author: CM
 * Custom CM rules for Modernizr.
 */

(function() {
  Modernizr.addTest('inputinfixed', function() {
    return !navigator.userAgent.match(/(iPad|iPhone|iPod)/i);
  });
})();
