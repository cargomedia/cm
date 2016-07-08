/**
 * @class CM_StreamChannel_Definition
 * @extends CM_Frontend_JsonSerializable
 */
var CM_StreamChannel_Definition = CM_Frontend_JsonSerializable.extend({

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
  }
});
