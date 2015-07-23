/*
 * Author: CM
 */

(function(global) {

  /**
   * @callback PromiseThrottled
   * @param {*} any number of optional params
   * @return {Promise}
   */

  /**
   * @param {PromiseThrottled} fn
   * @param {Object|null} [options]
   * @param {Boolean} options.cancelLeading Whether to cancel the previous promise if it is still running.
   * @param {Boolean} options.cancelTrailing Whether to cancel the next promises if the current is not yet finished.
   * @param {Boolean} options.queue Whether to queue the next promises.
   * @param {String} options.key A custom key to store the resulted PromiseThrottled.
   * @returns {PromiseThrottled}
   */
  function promiseThrottler(fn, options) {
    options = _.defaults(
      options || {}, {
        cancelLeading: false,
        cancelTrailing: false,
        queue: false
      }
    );
    if ((+options.cancelLeading) + (+options.cancelTrailing) + (+options.queue) > 1) {
      throw new Error('PromiseThrottler options "cancelLeading", "cancelTrailing", "queue" exclude each other.');
    }
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
      if (options.cancelLeading && promise && promise.isPending() && promise.isCancellable()) {
        promise.cancel();
        promise = null;
      }
      if (options.cancelTrailing && promise && promise.isPending()) {
        return Promise.reject(new Promise.CancellationError);
      }
      if (options.queue && promise && promise.isPending()) {
        var args = arguments;
        promise = promise.finally(function() {
          return fn.apply(null, args);
        });
      }
      if (!promise || !promise.isPending()) {
        promise = fn.apply(null, arguments);
      }
      return promise;
    };
  }

  var storage = {};

  /**
   * @see promiseThrottler
   */
  function namespaceThrottler(namespace, fn, options) {
    if (!storage[namespace]) {
      storage[namespace] = nonameThrottler(fn, options);
    }
    return storage[namespace];
  }

  global.promiseThrottler = promiseThrottler;

})(window);
