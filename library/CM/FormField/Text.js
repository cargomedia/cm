/**
 * @class CM_FormField_Text
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Text = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Text',

	events: {
		'blur input': function() {
			this.trigger('blur');
		},
		'focus input': function() {
			this.trigger('focus');
		}
	},

	/**
	 * @param {String} value
	 */
	setValue: function(value) {
		this.$('input').val(value);
	},

	setFocus: function() {
		this.$('input').focus();
	}
});
