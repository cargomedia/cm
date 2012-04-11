ready: function() {
},

validate: function() {
	var value = this.getValue();
	if (null === value) {
		this.error();
		return;
	}
	this.ajax('validate', {'userInput': value} , {
		success: function () {
			this.error();
		},
		error: function(msg, type) {
			if ('CM_Exception_FormFieldValidation' == type) {
				this.error(msg);
				return false;
			}
		}
	});
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
 * @return string|null
 */
getValue: function() {
	var formData = this.getForm().$().serializeArray();
	return _.find(formData, function(fieldData) {
		return fieldData.name == this.getName();
	}, this).value || null;
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
