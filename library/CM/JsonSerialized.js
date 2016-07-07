/**
 * @class CM_JsonSerialized_Abstract
 * @extends Backbone.Model
 */
var CM_JsonSerialized_Abstract = Backbone.Model.extend({

  _class: 'CM_JsonSerialized_Abstract',

  /**
   * @param {CM_JsonSerialized_Abstract} jsonSerialized
   * @returns {{removed: Array, added: Object, updated: Object}}
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

    if (!this.equals(jsonSerialized)) {
      var keys = _.union(this.keys(), jsonSerialized.keys());
      _.each(keys, function(key) {
        var localValue = this.get(key);
        var externalValue = jsonSerialized.get(key);
        var resultTarget = this.has(key) ? result.updated : result.added;

        if (!jsonSerialized.has(key)) {
          if (this.compatible(localValue)) {
            localValue.trigger('remove');
          }
          result.removed.push(key);
          this.unset(key);

        } else if (this.compatible(localValue)) {
          var resultChild = localValue.sync(externalValue);
          if (_.any(resultChild, function(value) {
              return !_.isEmpty(value);
            })) {
            resultTarget[key] = resultChild;
          }
        } else {
          this.set(key, externalValue);
          var attrs = {};
          _.each(this.changedAttributes(), function(val, key) {
            attrs[key] = val instanceof CM_JsonSerialized_Abstract ? val.toJSON() : val;
          });
          _.extend(resultTarget, attrs);
        }
      }, this);

      _.each(result, function(val, key) {
        if (_.isEmpty(val)) {
          delete result[key];
        }
      });

      this.trigger('sync', this, result);
    }
    return result;
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
  }
});
