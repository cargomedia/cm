/*
 * Author: CM
 */

(function(global) {

  var slice = Array.prototype.slice;

  var storage = {};

  /**
   * @param {Function} fn
   * @param {String} [namespace]
   * @returns {Function}
   */
  function promiseOnce(fn, namespace) {
    var uid = namespace || _getUid();
    if(storage[uid]) {
      throw new Error('`' + uid + '` namespace is already registered for another Promise.');
    }
    return function() {
      return _getPromiseOnce.apply(this, [fn, uid].concat(slice.call(arguments)));
    };
  }

  /**
   * @param {Promise|String} promiseIdentifier
   */
  function removePromiseOnce(promiseIdentifier) {
    if (promiseIdentifier instanceof Promise) {
      var uid;
      for (uid in storage) {
        if (promiseIdentifier === storage[uid]) {
          delete storage[uid];
        }
      }
    } else {
      delete storage[promiseIdentifier];
    }
  }

  /**
   * @param {Function} fn
   * @param {String} uid
   * @param {*...} fn arguments
   * @returns {Promise}
   * @private
   */
  function _getPromiseOnce(fn, uid) {
    if (!storage[uid]) {
      var promise = fn.apply(this, slice.call(arguments, 2));
      if(!(promise instanceof Promise)) {
        throw new Error('promiseOnce getter function must return a Promise instance.');
      }
      storage[uid] = promise;
    }
    return storage[uid];
  }

  /**
   * @returns {String}
   * @private
   */
  function _getUid() {
    return (Math.random() + 1).toString(36).substring(7);
  }

  global.promiseOnce = promiseOnce;
  global.removePromiseOnce = removePromiseOnce;

})(window);
