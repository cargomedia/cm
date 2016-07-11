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
  },

  /**
   * @param {String} eventName
   * @param {Function} callback fn(array data)
   * @param {Object} [context]
   * @param {Boolean} [allowClientMessage]
   */
  addStreamEventListener: function(eventName, callback, context, allowClientMessage) {
    cm.stream.bind(this.getKey(), this.getType(), eventName, callback, context, allowClientMessage);
  },

  /**
   * @param {String} [eventName]
   * @param {Function} [callback]
   * @param {Object} [context]
   */
  removeStreamEventListener: function(eventName, callback, context) {
    cm.stream.unbind(this.getKey(), this.getType(), eventName, callback, context);
  }
});
