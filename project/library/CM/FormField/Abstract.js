/**
 * @class CM_FormField_Abstract
 * @extends CM_View_Abstract
 */
var CM_FormField_Abstract = CM_View_Abstract.extend({

	/** @type String */
	_class: 'CM_FormField_Abstract',

	ready: function() {
	},
	
	validate: function() {
		var value = this.getValue();
		if (_.isEmpty(value)) {
			this.error();
			return;
		}
		this.ajax('validate', {'userInput': value} , {
			success: function () {
				if (value != this.getValue()) {
					return false;
				}
				this.error();
			},
			error: function(msg, type) {
				if (value != this.getValue()) {
					return false;
				}
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
	 * @return String
	 */
	getName: function() {
		return this.options.name;
	},
	
	/**
	 * @return string|null
	 */
	getValue: function() {
		var formData = this.getForm().getData();
		if (!_.has(formData, this.getName())) {
			return null;
		}
		return formData[this.getName()];
	},
	
	/**
	 * @return Object
	 */
	getOptions: function() {
		return this.options.options;
	},
	
	/**
	 * @param {String} name
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
	 * @param {String|Null} message
	 */
	error: function(message) {
		var $container = this.$('.messages');
		$container.html('');
	
		if (message) {
			if ($container.length) {
				$container.append('<div class="form_field_error" style="display:none"></div><br clear="all" />')
				.children('.form_field_error').html(message).fadeIn('fast');
			} else {
				cm.error.trigger('FormField `' + this.getName() + '`: ' + message);
			}
	
			this.$('input, select, textarea').focus();
		}
	}
});