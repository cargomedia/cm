/**
 * @class CM_FormField_Abstract
 * @extends CM_View_Abstract
 */
var CM_FormField_Abstract = CM_View_Abstract.extend({
	_class: 'CM_FormField_Abstract',

	ready: function() {
	},

	validate: function() {
		var value = this.getValue();
		if (_.isEmpty(value)) {
			this.error();
			return;
		}
		this.ajax('validate', {'userInput': value, 'form': this.getForm().getClass(), 'fieldName': this.getName()}, {
			success: function() {
				if (value != this.getValue()) {
					this.removeError();
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

	reset: function() {
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
		if (!options[name]) {
			return null;
		}
		return options[name];
	},

	/**
	 * @param {String|Null} message
	 */
	error: function(message) {
		var $container = this.$('.messages');
		var $errorMessage = $container.find('.formField-error');
		this.$el.removeClass('hasError');

		if (message) {
			if ($container.length) {
				this.$el[0].offsetWidth;	// Force reflow for CSS-animation
				this.$el.addClass('hasError');

				if ($errorMessage.length) {
					$errorMessage.html(message);
				} else {
					$errorMessage = $('<div class="formField-error"></div>').hide().appendTo($container);
					$errorMessage.html(message);
					$errorMessage.slideDown('fast');
				}
			} else {
				cm.error.trigger('FormField `' + this.getName() + '`: ' + message);
			}
		} else {
			$errorMessage.remove();
		}
	},

	removeError: function() {
		var $formInput = this.$('input, select, textarea, .textinput');
		var $container = this.$('.messages');
		var $errorMessage = $('<div class="formField-error"></div>');

		$formInput.removeClass('hasError');
		$errorMessage.slideUp('fast', function() {
			$container.html('');
		});
	}
});
