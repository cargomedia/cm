/**
 * @class CM_JsonSerialized_Abstract
 * @extends Backbone.Model
 */
var CM_JsonSerialized_Abstract = Backbone.Model.extend({

  _class: 'CM_JsonSerialized_Abstract',

  /**
   * @param {CM_JsonSerialized_Abstract} jsonSerialized
   */
  sync: function(jsonSerialized) {
    if (!this.compatible(jsonSerialized)) {
      throw Error('Failed to update the model, incompatible parameter.');
    }

    var current = null;
    var syncAttributes = {};
    var syncChildAttributes = null;

    if (!this.equals(jsonSerialized)) {
      jsonSerialized.each(function(value, key) {
        current = this.get(key);
        if (this.compatible(current)) {
          syncChildAttributes = current.sync(value);
          if (!_.isEmpty(syncChildAttributes)) {
            syncAttributes[key] = syncChildAttributes;
          }
        } else {
          this.set(key, value);
          _.extend(syncAttributes, this.changedAttributes());
        }
      }, this);
      this.trigger('sync', this, syncAttributes);
    }
    return syncAttributes;
  },

  fetch: function() {
    throw new Error('Not implemented.');
  },

  /**
   * @returns {String}
   */
  getClass: function() {
    return this._class;
  },

  destruct: function() {
  },

  /**
   * @returns {Object}
   */
  toJSON: function() {
    var data = Backbone.Model.prototype.toJSON.apply(this, arguments);
    _.each(data, function(value, key) {
      if (this.compatible(value)) {
        data[key] = value.toJSON();
      }
    }, this);
    return data;
  },

  /**
   * @param {CM_JsonSerialized_Abstract|*} value
   * @returns {Boolean}
   */
  compatible: function(value) {
    return value instanceof CM_JsonSerialized_Abstract;
  },

  /**
   * @param {CM_JsonSerialized_Abstract|*} jsonSerialized
   * @returns {Boolean}
   */
  equals: function(jsonSerialized) {
    if (!this.compatible(jsonSerialized)) {
      return false;
    }

    var keys = _.union(this.keys(), jsonSerialized.keys());

    return _.every(keys, function(key) {
      var localValue = this.get(key);
      var externalValue = jsonSerialized.get(key);
      if (this.compatible(externalValue)) {
        return externalValue.equals(localValue);
      } else {
        return _.isEqual(externalValue, localValue);
      }
    }, this);
  },

  /**
   * @param {CM_JsonSerialized_Abstract~eachCallback} callback
   * @param {*} [context]
   */
  each: function(callback, context) {
    /**
     * @callback CM_JsonSerialized_Abstract~eachCallback
     * @param {*} value
     * @param {String} key
     */
    _.each(this.attributes, callback, context || this);
  }
});
