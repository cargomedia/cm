var CM_Frontend_SynchronizableTrait = _.clone(CM_Frontend_AbstractTrait);

CM_Frontend_SynchronizableTrait.traitProperties = {
  /**
   * @param {*} obj
   * @returns {{removed: Array, added: Object, updated: Object}|null}
   */
  sync: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @param {*} value
   * @returns {Boolean}
   */
  equals: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @returns {Object}
   */
  toJSON: CM_Frontend_AbstractTrait.abstractMethod,

  /**
   * @param {*} value
   * @returns {Boolean}
   */
  isCompatible: function(value) {
    return CM_Frontend_SynchronizableTrait.isImplementedBy(value);
  }
};

