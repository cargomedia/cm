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
	},

	enableTriggerChange: function() {
		var self = this;
		var $input = this.$('input');
		var valueLast = $input.val();
		var callback = function() {
			var value = this.value;
			if (value != valueLast) {
				valueLast = value;
				self.trigger('change');
			}
		};
		// IE9: `propertychange` and `keyup` needed additionally
		$input.on('input propertychange keyup', callback);
	}
});
