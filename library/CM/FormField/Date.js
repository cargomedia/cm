/**
 * @class CM_FormField_Date
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Date = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Date',

	setFocus: function() {
		this.$('select').first().focus();
	},

	ready: function() {
		this.bindJquery(this.$('select'), 'change', function() {
			this.trigger('change');
		});
	}
});
