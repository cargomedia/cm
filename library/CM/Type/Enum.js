/**
 * @class CM_Type_Enum
 * @extends CM_Class_Abstract
 */
var CM_Type_Enum = CM_Class_Abstract.extend({

  _class: 'CM_Type_Enum',

  /** @type {*} */
  _value: null,

  /**
   * @param {{value: *}|*} data
   */
  constructor: function(data) {
    var value = _.isObject(data) && 'value' in data ? data.value : data;
    if (!this._isValidValue(value)) {
      throw new Error('Invalid enum value `' + value + '` for ' + this._class);
    }
    this._value = value;
  },

  /**
   *
   * @param {*} value
   * @returns {Boolean}
   * @private
   */
  _isValidValue: function(value) {
    var classProperties = this.constructor;
    return _.any(classProperties.getConstantList(), function(enumKey) {
      return value === classProperties[enumKey];
    });
  },

  /**
   * @returns {String}
   */
  toString: function() {
    return String(this._value);
  }
}, {
  /**
   * return {Array}
   */
  getConstantList: function() {
    var classProperties = this;
    return _
      .chain(Object.getOwnPropertyNames(classProperties))
      .difference(Object.getOwnPropertyNames(CM_Class_Abstract))
      .filter(function(key) {
        return !_.isObject(classProperties[key]) && !_.isFunction(classProperties[key]);
      })
      .value();
  }
});
