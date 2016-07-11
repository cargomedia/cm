/**
 * @class CM_Frontend_JsonSerializable
 * @extends Backbone.Model
 * @mixes CM_Frontend_SynchronizableTrait
 */
var CM_Frontend_JsonSerializable = Backbone.Model.extend({

  _class: 'CM_Frontend_JsonSerializable',

  constructor: function() {
    // TODO: move me out of the constructor when CM will use CommonJS module... ;(
    CM_Frontend_SynchronizableTrait.applyImplementation(CM_Frontend_JsonSerializable.prototype);
    return Backbone.Model.prototype.constructor.apply(this, arguments);
  },

  /**
   * @param {CM_Frontend_JsonSerializable} jsonSerialized
   * @returns {{removed: Array, added: Object, updated: Object}|null}
   */
  sync: function(jsonSerialized) {
    if (!this.isSynchronizable(jsonSerialized)) {
      throw Error('Failed to update the model, incompatible parameter.');
    }

    var result = {
      removed: [],
      added: {},
      updated: {}
    };
    var resultCleanup = function(result) {
      _.each(result, function(val, key) {
        if (_.isEmpty(val)) {
          delete result[key];
        }
      });
      return _.isEmpty(result) ? null : result;
    };

    if (!this.equals(jsonSerialized)) {
      var keys = _.union(this.keys(), jsonSerialized.keys());
      _.each(keys, function(key) {
        var localValue = this.get(key);
        var externalValue = jsonSerialized.get(key);
        var resultTarget = this.has(key) ? result.updated : result.added;

        if (!jsonSerialized.has(key)) {
          if (_.isObject(localValue) && _.isFunction(localValue.trigger)) {
            localValue.trigger('remove');
          }
          result.removed.push(key);
          this.unset(key);
        } else if (this.isSynchronizable(localValue) && localValue.isSynchronizable(externalValue)) {
          var resultChild = localValue.sync(externalValue);
          if (resultChild) {
            resultTarget[key] = resultChild;
          }
        } else if (!_.isEqual(localValue, externalValue)) {
          this.set(key, externalValue);
          var attrs = {};
          attrs[key] = externalValue;
          _.extend(resultTarget, attrs);
        }
      }, this);
    }

    if (result = resultCleanup(result)) {
      this.trigger('sync', this, result);
    }
    return result;
  },

  /**
   * @param {CM_Frontend_JsonSerializable|*} jsonSerialized
   * @returns {Boolean}
   */
  equals: function(jsonSerialized) {
    if (!this.isSynchronizable(jsonSerialized)) {
      return false;
    }
    var keys = _.union(this.keys(), jsonSerialized.keys());
    return _.every(keys, function(key) {
      var localValue = this.get(key);
      var externalValue = jsonSerialized.get(key);
      if (this.isSynchronizable(externalValue)) {
        return externalValue.equals(localValue);
      } else {
        return _.isEqual(externalValue, localValue);
      }
    }, this);
  },

  /**
   * @returns {Object}
   */
  toJSON: function() {
    var encode = function(data) {
      _.each(data, function(value, key) {
        if (this.isSynchronizable(value)) {
          data[key] = value.toJSON();
        } else if (_.isArray(value)) {
          _.each(value, function(item, index) {
            data[key][index] = this.isSynchronizable(item) ? item.toJSON() : encode(item);
          }, this);
        }
      }, this);
      return data;
    }.bind(this);
    return encode(Backbone.Model.prototype.toJSON.apply(this, arguments));
  },

  /**
   * @returns {String}
   */
  getClass: function() {
    return this._class;
  },

  destruct: function() {
  },

  fetch: function() {
    throw new Error('Not implemented.');
  }
});
