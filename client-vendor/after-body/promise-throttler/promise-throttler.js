/*
 * Author: CM
 */

(function(global) {

  var storage = {};

  /**
   * @callback PromiseThrottled
   * @param {...Object|String|Number} any number of optional params
   * @return {Promise}
   */

  /**
   * @param {PromiseThrottled} fn
   * @param {Boolean} cancel
   * @returns {PromiseThrottled}
   */
  function promiseThrottler(fn, cancel) {
    var promise;
    return function() {
      if (cancel && promise && promise.isPending()) {
        promise.cancel();
      }
      if (!promise || !promise.isPending()) {
        promise = fn.apply(null, arguments);
      }
      return promise;
    };
  }

  global.promiseThrottler = promiseThrottler;

})(window);
