var Event = require('./event');

/**
 * @class PersistentStorage
 * @extends Event
 */
var PersistentStorage = Event.extend({

  /**
   * @param {String} name
   * @param {Storage} adapter
   */
  constructor: function(name, adapter) {
    this._name = name;
    this._data = {};
    this._adapter = null;
    if (this._isSupported(adapter)) {
      this._adapter = adapter;
      this.read();
    }
  },

  /**
   * @param {String} [name]
   * @returns {*}
   */
  get: function(name) {
    if (name) {
      return this._data[name];
    } else {
      return this._data;
    }
  },

  /**
   * @param {String|Object} key
   * @param {*} [value]
   */
  set: function(key, value) {
    var obj = {};
    if (_.isString(key)) {
      obj[key] = value;
    } else {
      obj = key;
    }
    _.extend(this._data, obj);
    this.write();
  },

  /**
   * @param {String} key
   * @returns {Boolean}
   */
  has: function(key) {
    return !!_.contains(_.keys(this._data), key);
  },

  /**
   * @param {String} name
   */
  remove: function(name) {
    delete this._data[name];
    this.write();
  },

  clear: function() {
    this._data = {};
    this.write();
  },

  delete: function() {
    if (this._adapter) {
      this._adapter.removeItem(this._name);
    }
  },

  read: function() {
    if (this._adapter) {
      this.set(JSON.parse(this._adapter.getItem(this._name)) || {});
    }
  },

  write: function() {
    if (this._adapter) {
      this._adapter.setItem(this._name, JSON.stringify(this._data));
    }
  },

  /**
   * @private
   */
  _isSupported: function(adapter) {
    try {
      var key = 'PersistentStorage._checkAdapterSupport:' + Math.random();
      var value = 'value-' + Math.random();
      adapter.setItem(key, value);
      if (adapter.getItem(key) !== value) {
        throw new Error('Failed to retrieve data from storage adapter');
      }
      adapter.removeItem(key);
      return true;
    } catch (error) {
      this._warning('Storage adapter not supported', error);
      return false;
    }
  },

  _warning: function() {
    var logger = cm && cm.logger ? cm.logger : console;
    logger.warn.apply(console, arguments);
  }
});


module.exports = PersistentStorage;
