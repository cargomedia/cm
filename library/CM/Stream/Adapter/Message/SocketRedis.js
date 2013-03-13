/**
 * @class CM_Stream_Adapter_Message_SocketRedis
 * @extends CM_Stream_Adapter_Message_Abstract
 */
var CM_Stream_Adapter_Message_SocketRedis = CM_Stream_Adapter_Message_Abstract.extend({

	/** @type {SocketRedis|Null} */
	_socketRedis: null,

	initialize: function(options) {
		this._socketRedis = new SocketRedis(options.sockjsUrl);
	},

	subscribe: function(channel, data, onmessage) {
		this._socketRedis.subscribe(channel, cm.options.renderStamp, data, onmessage);
	},

	unsubscribe: function(channel) {
		this._socketRedis.unsubscribe(channel);
	}
});
