/**
 * @class CM_Class_Abstract
 */
var CM_Class_Abstract = function() {
	this.initialize.apply(this, arguments);
};

CM_Class_Abstract.prototype = {
	/**
	 * @constructor
	 */
	initialize: function() {
	}
};

/**
 * @type {Function}
 * @param {Object} prototype
 */
CM_Class_Abstract.extend = Backbone.Model.extend;
