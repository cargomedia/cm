(function(global) {

  /**
   * @class CM_Exception
   * @extends Error
   *
   * @param {String} message
   * @param {Boolean} [isPublic]
   * @constructor
   */
  function CM_Exception(message, isPublic) {
    var temp = Error.call(this, message);
    this.name = 'CM_Exception';
    this.stack = temp.stack;
    this.message = temp.message;
    this.isPublic = isPublic;
    return this;
  }

  CM_Exception.prototype = Object.create(Error.prototype, {
    constructor: {
      value: CM_Exception,
      writable: true,
      configurable: true
    }
  });

  var exceptionMap = {};
  exceptionMap['CM_Exception'] = CM_Exception;

  /**
   * @param {String} className
   * @returns {Function} exception constructor
   */
  CM_Exception.extend = function(className) {
    var extension = function() {
      CM_Exception.apply(this, arguments);
      this.name = className;
    };
    extension.prototype = Object.create(CM_Exception.prototype);
    extension.prototype.constructor = CM_Exception;
    exceptionMap[className] = extension;
    return extension;
  };

  /**
   * @param {String} className
   * @returns {Function} exception constructor
   */
  CM_Exception.factory = function(className) {
    var extension = exceptionMap[className];
    if (!extension) {
      throw new CM_Exception('No such exception ' + className);
    }
    return extension;
  };

  global.CM_Exception_AuthRequired = CM_Exception.extend('CM_Exception_AuthRequired');
  global.CM_Exception_Nonexistent = CM_Exception.extend('CM_Exception_Nonexistent');
  global['CM_Exception'] = CM_Exception;

})(window);
