/*
 * Author: CM
 */

(function(global) {

  var storage = {};

  /**
   * @param {String} name
   * @param {Promise} promise
   * @returns {Promise}
   */
  function promiseThrottler(name, promise) {
    if (!storage[name]) {
      storage[name] = promise.finally(function() {
        delete storage[name];
      });
    }
    return storage[name];
  }

  global.promiseThrottler = promiseThrottler;

})(window);
