/**
 * @class CM_FormField_Hidden
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Hidden = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Hidden',

	/**
	 * @return String
	 */
	getValue: function() {
		return this.$('input').val();
	},

	/**
	 * @param {String} value
	 */
	setValue: function(value) {
		this.$('input').val(value);
	}
});
