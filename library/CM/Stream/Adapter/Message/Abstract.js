/**
 * @class CM_Stream_Adapter_Message_Abstract
 * @extends CM_Class_Abstract
 */
var CM_Stream_Adapter_Message_Abstract = CM_Class_Abstract.extend({
	/**
	 * @constructor
	 * @param {Object} options
	 */
	initialize: function(options) {

	},

	/**
	 * @param {String} channel
	 * @param {Object|Null} data
	 * @param {Function} onmessage
	 */
	subscribe: function(channel, data, onmessage) {
		throw 'Not implemented';
	},

	/**
	 * @param {String} channel
	 */
	unsubscribe: function(channel) {
		throw 'Not implemented';
	}
});
