/**
 * @namespace CM_Frontend_AbstractTrait
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
    _.each(this.getTraitProperties(), function(traitPropertyName) {
      var property = this.getTraitProperty(traitPropertyName);
      if (!(traitPropertyName in obj)) {
        if (property === this.abstractMethod) {
          throw new Error(traitPropertyName + ' not implemented.');
        } else {
          obj[traitPropertyName] = property;
        }
      }
    }, this);
    return obj;
  },

  /**
   * @param {*} value
   * @returns {Boolean}
   */
  isImplementedBy: function(value) {
    if (_.isObject(value)) {
      var traitProperties = this.getTraitProperties();
      return _.every(traitProperties, function(traitProperty) {
        return traitProperty in value;
      });
    }
    return false;
  },

  /**
   * @returns {Array}
   */
  getTraitProperties: function() {
    return Object.getOwnPropertyNames(this.traitProperties);
  },

  /**
   * @param {String} name
   * @returns {*}
   */
  getTraitProperty: function(name) {
    return this.traitProperties[name];
  },

  abstractMethod: function() {
    throw new Error('Abstract method.');
  }
};

