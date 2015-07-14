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
  function throttleFunction(fn, options) {
    options = _.defaults(options || {}, {cancel: false});
    var promise;

    return function() {
      if (promise && promise.isPending()) {
        if (options.cancel && promise.isCancellable()) {
          promise.cancel();
          promise = null;
        }
      }

      if (!promise || !promise.isPending()) {
        var result = fn.apply(null, arguments);
        if (!result instanceof Promise) {
          result = Promise.resolve(result);
        }
      }
      result.then(function() {
        promise = null;
      });
      return result;
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
