/*
 * Author: CM
 */

(function(global) {

  /**
   * @param {PromiseThrottled} fn
   * @param {Object|null} [options]
   * @param {Boolean} options.cancel Whether to cancel the previous promise if it is still running.
   * @returns {PromiseThrottled}
   */
  function promiseThrottler(fn, options) {
    options = _.defaults(options || {}, {cancel: false});
    if (options.key) {
      return namespaceThrottler(options.key, fn, options);
    } else {
      return nonameThrottler(fn, options);
    }
  }

  /**
   * @see promiseThrottler
   */
  function nonameThrottler(fn, options) {
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

  var storage = {};

  /**
   * @param {String} namespace
   * @param {PromiseThrottled} fn
   * @param {Object|null} options
   * @param {Boolean} options.cancel Whether to cancel the previous promise if it is still running
   * @returns {Promise}
   */
  function namespaceThrottler(namespace, fn, options) {
    if (!storage[namespace]) {
      storage[namespace] = nonameThrottler(fn, options);
    }
    return storage[namespace];
  }

  global.promiseThrottler = promiseThrottler;

})(window);
