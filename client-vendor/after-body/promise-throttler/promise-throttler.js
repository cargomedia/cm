/*
 * Author: CM
 */

(function(global) {

  /**
   * @callback PromiseThrottled
   * @param {...Object|String|Number} any number of optional params
   * @return {Promise}
   */

  /**
   * @param {PromiseThrottled} fn
   * @param {Object|null} options
   * @param {Boolean} options.cancel Whether to cancel the previous promise if it is still running.
   * @returns {PromiseThrottled}
   */
  function promiseThrottler(fn, options) {
    options = _.defaults(options || {}, {cancel: false});
    var promise;

    return function() {
      if (options.cancel && promise && promise.isPending() && promise.isCancellable()) {
        promise.cancel();
        promise = null;
      }
      if (!promise || !promise.isPending()) {
        promise = fn.apply(null, arguments);
      }
      return promise;
    };
  }

  var throttlersStorage = {};

  /**
   * @param {String} namespace
   * @param {PromiseThrottled} fn
   * @param {Object|null} options
   * @param {Boolean} options.cancel Whether to cancel the previous promise if it is still running
   * @returns {Promise}
   */
  function throttle(namespace, fn, options) {
    if (!throttlersStorage[namespace]) {
      throttlersStorage[namespace] = throttleFunction(fn, options);

    }
    return throttlersStorage[namespace]();
  }

  global.throttleFunction = throttleFunction;
  global.throttle = throttle;

  // temporary backward compatibility
  global.promiseThrottler = throttleFunction;

})(window);
