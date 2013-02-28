/**
 * @class CM_Stream_Adapter_Message_Abstract
 */
function CM_Stream_Adapter_Message_Abstract(url) {
	this._url = url;
	this._subscribes = {};
}

CM_Stream_Adapter_Message_Abstract.prototype = {

	/** @type {Object} */
	_subscribes: {},

	_open: function() {
		throw 'Not implemented';
	},

	/**
	 * @param {String} channel
	 * @param {Function} onmessage
	 */
	_subscribe: function(channel, onmessage) {
		throw 'Not implemented';
	},

	/**
	 * @param {String} channel
	 */
	_unsubscribe: function(channel) {
		throw 'Not implemented';
	},

	/**
	 * @param {String} channel
	 * @param {Function} onmessage
	 */
	subscribe: function(channel, onmessage) {
		if (this._subscribes[channel]) {
			return;
		}
		if (!this._isOpening() && !this._isOpen()) {
			this._open();
		}
		if (this._isOpen()) {
			this._subscribe(channel, onmessage);
		}
		this._subscribes[channel] = onmessage;
	},

	/**
	 * @param {String} channel
	 */
	unsubcribe: function (channel) {
		this._unsubscribe(channel);
	},

	_onOpen: function () {
		var handler = this;
		_.each(this._subscribes, function(onmessage, channel) {
			handler._subscribe(channel, onmessage);
		});
	}
};
