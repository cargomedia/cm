ready: function() {
},

/**
 * @param mixed value
 * @param boolean required
 */
validate: function(value, required) {
},

/**
 * @return CM_Form_Abstract
 */
getForm: function() {
	return this.getParent();
},

/**
 * @return jQuery
 */
$: function(selector) {
	if (!selector) {
		return this.$el;
	}
	return $(selector, this.el);
},

/**
 * @return string
 */
getName: function() {
	return this.options.name;
},

/**
 * @return object
 */
getOptions: function() {
	return this.options.options;
},

/**
 * @param string name
 * @return mixed|null
 */
getOption: function(name) {
	var options = this.getOptions();
	if (!options[name]) {
		return null;
	}
	return options[name];
},

/**
 * @param string|null message
 */
error: function(message) {
	var $container = this.$('.messages');
	$container.html('');

	if (message) {
		$container.append('<div class="form_field_error" style="display:none"></div><br clear="all" />')
		.children('.form_field_error').html(message).fadeIn('fast');

		this.$('input, select, textarea').focus();
	}
}
