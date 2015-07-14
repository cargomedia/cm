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
   * @param {Object|null} [options]
   * @param {Boolean} options.cancel Whether to cancel the previous promise if it is still running.
   * @returns {PromiseThrottled}
   */
  function throttleFunction(fn, options) {
    options = _.defaults(options || {}, {cancel: false});
    var promise;
    var laterPromiseToCancel;

    return function() {
      console.log('promise is ', promise);
      if (promise && promise.isPending()) {
        if (options.cancel && promise.isCancellable()) {
          console.log('promise cancelled');
          promise.cancel();
          promise = null;
        }
      }

      if (!promise || !promise.isPending()) {
        promise = fn.apply(null, arguments);
        console.log('promise created ', promise);
        if (!promise instanceof Promise) {
          console.log('promise resolved');
          promise = Promise.resolve();
        }
      } else if (!options.cancel) {
        if (!laterPromiseToCancel) {
          console.log('create redundant promise');
          laterPromiseToCancel = promise.finally(function() {
            console.log('cancel redundant promise');
            //redundantPromise.cancel();
            laterPromiseToCancel = null;
            promise = null;
            throw new Promise.CancellationError();
          });
        }
        console.log('return redundant promise');
        return laterPromiseToCancel;
      }
      console.log('return promise');
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


  /**
   * TODO to discuss.
   * It still has the problems.
   * If you make `test` cancellable then the console window in web inspector will tremble for real like it is under the fever.
   * If you make the difference between second .then timeout 500 and the Promise timeout 1500 then there is a chance that both .then will be executed cause
   * cancellation of the second .then will happen later than .then itself.
   */
  var test = throttleFunction(function() {
    return new Promise(function(resolve, reject) {
      setTimeout(function() {
        resolve(5);
      }, 1500);
    }).cancellable();
  }/*, {cancel: true}*/);

  test().then(function(result) {
    console.log('first test is finished ' + result);
  });

  setTimeout(function() {
    test().then(function(result) {
      console.log('second test is finished ' + result);
    });
  }, 500);

  test().then(function(result) {
    console.log('third test is finished ' + result);
  });

})(window);
