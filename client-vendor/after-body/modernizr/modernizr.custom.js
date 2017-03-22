/*
 * Author: CM
 * Custom CM rules for Modernizr.
 */

(function() {
  Modernizr.addTest('inputinfixed', function() {
    return !navigator.userAgent.match(/(iPad|iPhone|iPod)/i);
  });
  Modernizr.addTest('webvr', function() {
    return 'getVRDisplays' in navigator;
  });
  /*
   * test based on THREE.js method, see: http://threejs.org/examples/js/Detector.js
   */
  Modernizr.addTest('webgl', function() {
    try {
      var canvas = document.createElement('canvas');
      return !!( window.WebGLRenderingContext && ( canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (e) {
      return false;
    }
  });
  /**
   * See http://stackoverflow.com/questions/10672081/how-to-detect-if-browser-supports-plaintext-only-value-in-contenteditable-para/10672378#10672378
   */
  Modernizr.addTest('plaintextonly', function(){
    var d = document.createElement("div");
    try {
      d.contentEditable="PLAINtext-onLY";
    } catch(e) {
      return false;
    }
    return d.contentEditable=="plaintext-only";
  });
})();
