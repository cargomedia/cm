/**
 * @class CM_Stream_Adapter_Message_SocketRedis
 * @extends CM_Stream_Adapter_Message_Abstract
 */
function CM_Stream_Adapter_Message_SocketRedis(options) {
	this._socketRedis = new SocketRedis(options.sockjsUrl);
}

CM_Stream_Adapter_Message_SocketRedis.prototype = _.extend(CM_Stream_Adapter_Message_Abstract.prototype, {

	/** @type {SocketRedis|Null} */
	_socketRedis: null,

	/**
	 * @param {String} channel
	 * @param {Function} onmessage
	 */
	subscribe: function (channel, onmessage) {
		this._socketRedis.subscribe(channel, cm.options.renderStamp, {sessionId: $.cookie('sessionId')}, onmessage);
	},

	/**
	 * @param {String} channel
	 */
	unsubscribe: function(channel) {
		this._socketRedis.unsubscribe(channel);
	}
});
