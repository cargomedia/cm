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
  Modernizr.addTest('getusermedia', function() {
    var inMediaDevices = (navigator.mediaDevices && !!Modernizr.prefixed('getUserMedia', navigator.mediaDevices));
    var inNavigator = !!Modernizr.prefixed('getUserMedia', navigator);
    return inMediaDevices || inNavigator;
  });
})();
