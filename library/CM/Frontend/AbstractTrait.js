/**
 * @class CM_Frontend_AbstractTrait
 */
var CM_Frontend_AbstractTrait = {

  /**
   * @inner
   * @mixin
   */
  traitProperties: {},

  /**
   * @param {Object} obj
   * @returns {Object}
   */
  applyImplementation: function(obj) {
    if (!_.isObject(obj)) {
      throw new Error('Trait must be applied to an Object.');
    }
    var abstractMethod = this.abstractMethod;
    _.each(this.traitProperties, function(property, name) {
      if (!(name in obj)) {
        if (property === abstractMethod) {
          throw new Error(name + ' not implemented.');
        } else {
          obj[name] = property;
        }
      }
    });
    return obj;
  },

  /**
   * @param {*} value
   * @returns {Boolean}
   */
  isImplementedBy: function(value) {
    if (_.isObject(value)) {
      return _.every(this.traitProperties, function(property, name) {
        return name in value;
      });
    }
    return false;
  },

  abstractMethod: function() {
    throw new Error('Abstract method.');
  }
};

