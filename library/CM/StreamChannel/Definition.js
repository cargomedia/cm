/**
 * @class CM_StreamChannel_Definition
 * @extends Backbone.Model
 */
var CM_StreamChannel_Definition = Backbone.Model.extend({

  _class: 'CM_StreamChannel_Definition',

  /**
   * @returns {String}
   */
  getKey: function() {
    return this.get('key');
  },

  /**
   * @returns {Number}
   */
  getType: function() {
    return this.get('type');
  },

  /**
   * @param {*} definition
   * @returns {Boolean}
   */
  equals: function(definition) {
    return definition instanceof CM_StreamChannel_Definition && _.isEqual(this.toJSON(), definition.toJSON());
  },

  clear: function() {
    this.set({
      key: '',
      type: 0
    });
  }
});
