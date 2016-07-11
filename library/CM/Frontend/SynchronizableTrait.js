/**
 * @namespace CM_Frontend_SynchronizableTrait
 * @extends CM_Frontend_AbstractTrait
 */
var CM_Frontend_SynchronizableTrait = _.clone(CM_Frontend_AbstractTrait);

CM_Frontend_SynchronizableTrait.traitProperties = {
  /**
   * @param {*} obj
   * @returns {{removed: Array, added: Object, updated: Object}|null}
   * @abstract
   */
  sync: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @param {*} value
   * @returns {Boolean}
   * @abstract
   */
  equals: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @returns {Object}
   * @abstract
   */
  toJSON: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @param {*} value
   * @returns {Boolean}
   */
  isSynchronizable: function(value) {
    return CM_Frontend_SynchronizableTrait.isImplementedBy(value);
  }
};

