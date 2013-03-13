/**
 * @class CM_Stream_Adapter_Message_SocketRedis
 * @extends CM_Stream_Adapter_Message_Abstract
 * @constructor
 */
function CM_Stream_Adapter_Message_SocketRedis(options) {
	this._socketRedis = new SocketRedis(options.sockjsUrl);
}

CM_Stream_Adapter_Message_SocketRedis.prototype = _.extend(CM_Stream_Adapter_Message_Abstract.prototype, {

	/** @type {SocketRedis|Null} */
	_socketRedis: null,

	/**
	 * @param {String} channel
	 * @param {Object|Null} data
	 * @param {Function} onmessage
	 */
	subscribe: function (channel, data, onmessage) {
		this._socketRedis.subscribe(channel, cm.options.renderStamp, data, onmessage);
	},

	/**
	 * @param {String} channel
	 */
	unsubscribe: function(channel) {
		this._socketRedis.unsubscribe(channel);
	}
});
