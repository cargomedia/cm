/**
 * @class CM_Paging_Abstract
 * @extends Backbone.Collection
 */
var CM_Paging_Abstract = Backbone.Collection.extend({

  /** @type {String} */
  _class: 'CM_Paging_Abstract',

  model: CM_Model_Abstract,

  constructor: function(data) {
    if (!('items' in data) || !_.isArray(data.items)) {
      throw new Error(this._class + ' must be populated by an object with an `items` property.');
    }
    Backbone.Collection.prototype.constructor.call(this, data.items);
  }
});
