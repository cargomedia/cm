/**
 * @class CM_Paging_Bound
 * @extends CM_Paging_Abstract
 */
var CM_Paging_Bound = CM_Paging_Abstract.extend({

  /** @type {String} */
  _class: 'CM_Paging_Bound',

  model: CM_Model_Abstract,

  streamEvents: {
    'CREATE': function(model) {
      this.add(model);
    },
    'UPDATE': function(model) {
      this.add(model, {merge: true});
    },
    'DELETE': function(model) {
      this.remove(model);
    }
  },

  constructor: function(data) {
    if ('streamChannel' in data) {
      this._bindStreams(this.streamEvents, data.streamChannel.key, data.streamChannel.type);
    }
    CM_Paging_Abstract.prototype.constructor.call(this, data);
  },

  /**
   * @param {{String: Function}} events
   * @param {String} channelKey
   * @param {String} channelType
   * @private
   */
  _bindStreams: function(events, channelKey, channelType) {
    _.each(events, function(callbackResponse, event) {
      this._bindStream(channelKey, channelType, event, callbackResponse);
    }, this);
  },

  /**
   * @param {String} channelKey
   * @param {Number} channelType
   * @param {String} event
   * @param {Function} callback fn(array data)
   * @param {Boolean} [allowClientMessage]
   */
  _bindStream: function(channelKey, channelType, event, callback, allowClientMessage) {
    cm.stream.bind(channelKey, channelType, event, callback, this, allowClientMessage);
    this.on('destruct', function() {
      cm.stream.unbind(channelKey, channelType, event, callback, this);
    }, this);
  }
});
