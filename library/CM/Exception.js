/**
 * @class CM_Exception
 * @extends Error
 *
 * @param {String} message
 * @param {String} [type]
 * @param {Boolean} [isPublic]
 * @constructor
 */
function CM_Exception(message, type, isPublic) {
  var temp = Error.call(this, message);
  temp.name = this.name = 'CM_Exception';
  this.stack = temp.stack;
  this.message = temp.message;
  this.type = type;
  this.isPublic = isPublic;
}

CM_Exception.prototype = Object.create(Error.prototype, {
  constructor: {
    value: CM_Exception,
    writable: true,
    configurable: true
  }
});
