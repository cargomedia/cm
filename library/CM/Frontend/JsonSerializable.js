/**
 * @class CM_Frontend_JsonSerializable
 * @extends Backbone.Model
 */
var CM_Frontend_JsonSerializable = Backbone.Model.extend({

  _class: 'CM_Frontend_JsonSerializable',

  /**
   * @param {CM_Frontend_JsonSerializable} jsonSerialized
   * @returns {{removed: Array, added: Object, updated: Object}|null}
   */
  sync: function(jsonSerialized) {
    if (!this.compatible(jsonSerialized)) {
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
      var defaultKeys = _(_.result(this, 'defaults', {})).keys();
      var keys = _.union(this.keys(), jsonSerialized.keys());
      _.each(keys, function(key) {
        var localValue = this.get(key);
        var externalValue = jsonSerialized.get(key);
        var resultTarget = this.has(key) ? result.updated : result.added;

        if (!jsonSerialized.has(key)) {
          if (!_.contains(defaultKeys, key)) {
            if (this.compatible(localValue)) {
              localValue.trigger('remove');
            }
            result.removed.push(key);
            this.unset(key);
          }
        } else if (this.compatible(localValue)) {
          var resultChild = localValue.sync(externalValue);
          if (resultChild) {
            resultTarget[key] = resultChild;
          }
        } else if (!_.isEqual(localValue, externalValue)) {
          this.set(key, externalValue);
          var attrs = {};
          attrs[key] = this.compatible(externalValue) ? externalValue.toJSON() : externalValue;
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
   * @returns {Object}
   */
  toJSON: function() {
    var encode = function(data) {
      _.each(data, function(value, key) {
        if (this.compatible(value)) {
          data[key] = value.toJSON();
        } else if (_.isArray(value)) {
          _.each(value, function(item, index) {
            data[key][index] = this.compatible(item) ? item.toJSON() : encode(item);
          }, this);
        }
      }, this);
      return data;
    }.bind(this);
    return encode(Backbone.Model.prototype.toJSON.apply(this, arguments));
  },

  /**
   * @param {CM_Frontend_JsonSerializable|*} value
   * @returns {Boolean}
   */
  compatible: function(value) {
    return value instanceof CM_Frontend_JsonSerializable;
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
