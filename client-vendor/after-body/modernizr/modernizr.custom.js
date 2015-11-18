/*
 * Author: CM
 * Custom CM rules for Modernizr.
 */

(function() {
  Modernizr.addTest('inputinfixed', function() {
    return !navigator.userAgent.match(/(iPad|iPhone|iPod)/i);
  });
  Modernizr.addTest('webvr', function() {
    return ('getVRDevices' in navigator) || ('mozGetVRDevices' in navigator);
  });
})();
