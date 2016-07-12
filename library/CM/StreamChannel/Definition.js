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
   * @param {String} action
   * @param {CM_StreamChannel_Definition~actionCallback} callback
   * @param {*} [context]
   */
  addActionEventListener: function(action, callback, context) {
    action = this._extractActionInfo(action);
    cm.action.bind(action.verb, action.type, this.getKey(), this.getType(), function(response) {
      /**
       * @callback CM_StreamChannel_Definition~actionCallback
       * @param {Object} action
       * @param {CM_Model_Abstract} model
       * @param {Array} data
       */
      callback.call(context, response.action, response.model, response.data);
    });
  },

  /**
   * @param {String} action
   */
  removeActionEventListeners: function(action) {
    action = this._extractActionInfo(action);
    cm.action.unbind(action.verb, action.type, this.getKey(), this.getType());
  },

  /**
   * @param {String} eventName
   * @param {CM_StreamChannel_Definition~streamCallback} callback
   * @param {Object} [context]
   * @param {Boolean} [allowClientMessage]
   */
  addStreamEventListener: function(eventName, callback, context, allowClientMessage) {
    /**
     * @callback CM_StreamChannel_Definition~streamCallback
     * @param {Array} data
     */
    cm.stream.bind(this.getKey(), this.getType(), eventName, callback, context, allowClientMessage);
  },

  /**
   * @param {String} [eventName]
   * @param {CM_StreamChannel_Definition~streamCallback} [callback]
   * @param {Object} [context]
   */
  removeStreamEventListeners: function(eventName, callback, context) {
    cm.stream.unbind(this.getKey(), this.getType(), eventName, callback, context);
  },

  /**
   * @param {String} action
   * @returns {{type: Number, verb: String}}
   * @private
   */
  _extractActionInfo: function(action) {
    if (!/^(\S+)\s+(.+)$/.test(action)) {
      throw new Error('StreamChannel action syntax error.');
    }
    var match = action.match(/^(\S+)\s+(.+)$/);
    if (!_.isNumber(cm.action.types[match[1]])) {
      throw new Error('StreamChannel action type not found.');
    }
    return {
      type: cm.action.types[match[1]],
      verb: match[2]
    };
  }
});
