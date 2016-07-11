/**
 * @class CM_Paging_List
 * @extends Backbone.Collection
 * @mixes CM_Frontend_SynchronizableTrait~traitProperties
 */
var CM_Paging_List = Backbone.Collection.extend({

  _class: 'CM_Paging_List',

  constructor: function() {
    // TODO: move me out of the constructor when CM will use CommonJS module... ;(
    CM_Frontend_SynchronizableTrait.applyImplementation(CM_Paging_List.prototype);
    return Backbone.Collection.prototype.constructor.apply(this, arguments);
  },

  model: function(attrs, options) {
    return new CM_Frontend_JsonSerializable(attrs, options);
  },

  /**
   * @param {CM_Paging_List} list
   * @returns {{removed: Array, added: Array, updated: Object}|null}
   */
  sync: function(list) {
    if (!(list instanceof CM_Paging_List)) {
      throw Error('Failed to update the collection, incompatible parameter.');
    }
    var result = {
      removed: [],
      added: [],
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

    list
      .chain()
      .filter(function(item) {
        return !!this.get(item);
      }, this)
      .each(function(itemToMerge) {
        var localItem = this.get(itemToMerge);
        if (localItem.isSynchronizable(itemToMerge) && !localItem.equals(itemToMerge)) {
          result.updated[localItem.id] = localItem.sync(itemToMerge);
        }
      }, this);

    list
      .chain()
      .filter(function(item) {
        return !this.get(item);
      }, this)
      .each(function(itemToAdd) {
        this.add(itemToAdd);
        result.added.push(itemToAdd);
      }, this);

    this
      .chain()
      .filter(function(item) {
        return !list.get(item);
      }, this)
      .each(function(itemToRemove) {
        this.remove(itemToRemove);
        result.removed.push(itemToRemove);
      }, this);

    if (result = resultCleanup(result)) {
      this.trigger('list:sync', this, result);
    }
    return result;
  },

  equals: function(list) {
    if (!list || !(list instanceof CM_Paging_List) || this.size() !== list.size()) {
      return false;
    }

    return list.any(function(item) {
      var localItem = this.get(item);
      if (localItem) {
        if (this.isSynchronizable(localItem) && this.isSynchronizable(item)) {
          return localItem.equals(item);
        } else {
          return _.isEqual(localItem, item);
        }
      }
      return false;
    }, this);
  },

  toJSON: function() {
    return Backbone.Collection.prototype.toJSON.apply(this, arguments);
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
