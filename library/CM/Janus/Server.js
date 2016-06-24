/**
 * @class CM_Janus_Server
 * @extends Backbone.Model
 */
var CM_Janus_Server = Backbone.Model.extend({

  _class: 'CM_Janus_Server',

  /**
   * @returns {String}
   */
  getWebSocketAddress: function() {
    return this._appendContext(this.get('webSocketAddress'));
  },

  /**
   * @param {String} webSocketAddress
   */
  setWebSocketAddress: function(webSocketAddress) {
    this.set('webSocketAddress', webSocketAddress);
  },

  /**
   * @returns {String}
   */
  getWebSocketAddressSubscribeOnly: function() {
    return this._appendContext(this.get('webSocketAddressSubscribeOnly'));
  },

  /**
   * @param {String} webSocketAddressSubscribeOnly
   */
  setWebSocketAddressSubscribeOnly: function(webSocketAddressSubscribeOnly) {
    this.set('webSocketAddressSubscribeOnly', webSocketAddressSubscribeOnly);
  },

  /**
   * @returns {String[]}
   */
  getIceServerList: function() {
    return this.get('iceServerList');
  },

  /**
   * @param {String[]} iceServerList
   */
  setIceServerList: function(iceServerList) {
    this.set('iceServerList', iceServerList);
  },

  /**
   * @param {String} address
   * @returns {String}
   * @private
   */
  _appendContext: function(address) {
    var separator = /\?[^\?]*$/.test(address) ? '&' : '?';
    return address + separator + jQuery.param({
        context: JSON.stringify(cm.getContext())
      });
  }
});
