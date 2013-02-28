/**
 * @class CM_Stream_Adapter_Message_SocketRedis
 * @extends CM_Stream_Adapter_Message_Abstract
 */
function CM_Stream_Adapter_Message_SocketRedis(url) {
	this._url = url;
	this._subscribes = {};
	this._connected = false;
}

CM_Stream_Adapter_Message_SocketRedis.prototype = _.extend(CM_Stream_Adapter_Message_Abstract.prototype, {

	/** @type {SocketRedis|Null} */
	_socketRedis: null,

	/** @type {Boolean} */
	_connected: null,

	_open: function() {
		var handler = this;
		this._socketRedis = new SocketRedis(this._url);
		this._socketRedis.onopen = function() {
			handler._onOpen.call(handler);
			this._connected = true;
		};
	},

	/**
	 * @return {Boolean}
	 */
	_isOpening: function () {
		return this._socketRedis && !this._connected;
	},

	/**
	 * @return {Boolean}
	 */
	_isOpen: function () {
		return this.connected;
	},

	/**
	 * @param {String} channel
	 * @param {Function} onmessage
	 */
	_subscribe: function (channel, onmessage) {
		this._socketRedis.subscribe(channel, cm.options.renderStamp, {sessionId: $.cookie('sessionId')}, onmessage);
	},

	/**
	 * @param {String} channel
	 */
	unsubscribe: function(channel) {
		this._socketRedis.unsubscribe(channel);
	}
});
