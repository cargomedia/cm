var Event = require('./event');
var AdapterMemory = require('./adapter/memory');

/**
 * @class PersistentStorage
 * @extends Event
 */
var PersistentStorage = Event.extend({

  /**
   * @param {String} name
   * @param {Storage} adapter
   * @param {*} [logger]
   */
  constructor: function(name, adapter, logger) {
    this._name = name;
    this._data = {};
    this._adapter = null;
    this._logger = logger && logger.warn ? logger : console;
    if (this._isSupported(adapter)) {
      this._adapter = adapter;
    } else {
      this._adapter = new AdapterMemory();
    }
  },

  /**
   * @param {String} [name]
   * @returns {*}
   */
  get: function(name) {
    var obj = this.read();
    if (name) {
      return obj[name];
    } else {
      return obj;
    }
  },

  /**
   * @param {String|Object} key
   * @param {*} [value]
   */
  set: function(key, value) {
    var obj = this.read();
    if (!_.isUndefined(value)) {
      obj[key] = value;
    } else {
      _.extend(obj, key);
      obj = key;
    }
    this.write(obj);
  },

  /**
   * @param {String} key
   * @returns {Boolean}
   */
  has: function(key) {
    var obj = this.read();
    return !!_.contains(_.keys(obj), key);
  },

  /**
   * @param {String} name
   */
  remove: function(name) {
    var obj = this.read();
    delete obj[name];
    this.write(obj);
  },

  clear: function() {
    this.write();
  },

  delete: function() {
    this._adapter.removeItem(this._name);
  },

  /**
   * @returns {Object}
   */
  read: function() {
    var data = {};
    try {
      var rawData = this._adapter.getItem(this._name);
      if (!_.isUndefined(rawData)) {
        data = JSON.parse(rawData);
      }
    } catch (error) {
      this.getLogger().warn('Failed to parse the `%s` PersistentStorage', this._name);
    }
    return data;
  },

  /**
   * @param {Object} [obj]
   */
  write: function(obj) {
    obj = !_.isUndefined(obj) ? obj : {};
    this._adapter.setItem(this._name, JSON.stringify(obj));
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
      this._logger.warn('Storage adapter not supported', error);
      return false;
    }
  }
});


module.exports = PersistentStorage;
