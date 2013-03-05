/**
 * @class CM_Stream_Adapter_Message_Abstract
 */
function CM_Stream_Adapter_Message_Abstract(options) {
}

CM_Stream_Adapter_Message_Abstract.prototype = {

	/**
	 * @param {String} channel
	 * @param {Function} onmessage
	 */
	subscribe: function(channel, onmessage) {
		throw 'Not implemented';
	},

	/**
	 * @param {String} channel
	 */
	unsubscribe: function(channel) {
		throw 'Not implemented';
	}
};
