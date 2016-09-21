/**
 * @param {Object} [data]
 * @class AdapterMemory
 */
function AdapterMemory(data) {
  this._data = data || {};
}

AdapterMemory.prototype = {
  /**
   * @param {String} key
   * @param {*} value
   */
  setItem: function(key, value) {
    this._data[key] = value;
  },

  /**
   * @param {String} key
   * @returns {*|null}
   */
  getItem: function(key) {
    var value = this._data[key];
    return !_.isUndefined(value) ? value : null;
  },

  removeItem: function(key) {
    delete this._data[key];
  },

  clear: function() {
    Object.keys(this._data).forEach(function(key) {
      this.removeItem(key);
    }.bind(this));
  }
};

AdapterMemory.prototype.constructor = AdapterMemory;

module.exports = AdapterMemory;
