/**
 * @class CM_Form_Abstract
 * @extends CM_View_Abstract
 */
var CM_Form_Abstract = CM_View_Abstract.extend({
	_class: 'CM_Form_Abstract',

	_fields: {},
	
	ready: function() {
	},
	
	
	_ready: function() {
		this._fields = {};
		_.each(this.options.fields, function(fieldInfo, name) {
			// Lazy construct
			var $field = this.$("#"+name);
			if ($field.length) {
				var fieldClass = window[fieldInfo.className];
				this.registerField(name,  new fieldClass({"el": $field, "parent": this, "name": name, "options": fieldInfo.options}));
			}
		}, this);
	
		var handler = this;
	
		_.each(this.options.actions, function(action, name) {
			var $btn = $('#'+this.getAutoId()+'-'+name+'-button');
			$btn.on('click', {action: name}, function(event) {
				handler.submit(event.data.action);
				return false;
			});
		}, this);
	
		if (this.options.default_action) {
			this.$().submit(function(event) {
				handler.submit(handler.default_action);
				return false;
			});
		}
	
		this.ready();
		_.each(this.getChildren(), function(child) {
			child._ready();
		});
	},

	/**
	 * @param {String} name
	 * @param {CM_FormField_Abstract} field
	 */
	registerField: function(name, field) {
		this._fields[name] = field;

		field.on('change', function() {
			this.trigger('change');
		}, this);
	},
	
	/**
	 * @return CM_Component_Abstract
	 */
	getComponent: function() {
		return this.getParent();
	},
	
	/**
	 * @return CM_FormField_Abstract|null
	 */
	getField: function(name) {
		if (!this._fields[name]) {
			return null;
		}
		return this._fields[name];
	},
	
	/**
	 * @return jQuery
	 */
	$: function(selector) {
		if (!selector) {
			return this.$el;
		}
		selector = selector.replace('#', '#'+this.getAutoId()+'-');
		return $(selector, this.el);
	},
	
	/**
	 * @param {String|Null} actionName
	 */
	getData: function(actionName) {
		var form_data = this.$().serializeArray();
		var action = actionName ? this.options.actions[actionName] : null;
	
		var data = {};
		var regex = /^([\w\-]+)(\[([^\]]+)?\])?$/;
		var name, match;
	
		for (var i = 0, item; item = form_data[i]; i++) {
			match = regex.exec(item.name);
			name = match[1];
			item.value = item.value || '';
	
			if (action && typeof action.fields[name] == 'undefined') {
				continue;
			}
	
			if (!match[2]) {
				// Scalar
				data[name] = item.value;
			} else if (match[2] == '[]') {
				// Array
				if (typeof data[name] == 'undefined') {
					data[name] = [];
				}
				data[name].push(item.value);
			} else if (match[3]) {
				// Associative array
				if (typeof data[name] == 'undefined') {
					data[name] = {};
				}
				data[name][match[3]] = item.value;
			}
		}
	
		return data;
	},
	
	submit: function(actionName, confirmed, data, callbacks) {
		confirmed = confirmed || false;
		callbacks = callbacks || {};
		actionName = actionName || this.options.default_action;
		var action = this.options.actions[actionName];
		
		if (!confirmed) {
			$('.form_field_error', this.$())
				.next('br').remove()
				.andSelf().remove();
		}
	
		data = data || this.getData(actionName);
	
		var hasErrors = false;
		_.each(_.keys(action.fields).reverse(), function(fieldName) {
			var required = action.fields[fieldName];
			if (required && _.isEmpty(data[fieldName])) {
				var field = this.getField(fieldName);
				var errorMessage = 'Required';
				var $labels = $('label[for="' + field.getAutoId() + '-input"]');
				if ($labels.length) {
					errorMessage = cm.language.get('{$label} is required.', {label: $labels.first().text()});
				}
				field.error(errorMessage);
				hasErrors = true;
			}
		}, this);
		if (hasErrors) {
			return false;
		}
	
		var handler = this;
		if (action.confirm_msg && !confirmed) {
			cm.ui.confirm(cm.language.get(action.confirm_msg), function() {
				handler.submit(actionName, true, data);
			});
			return false;
		}
	
		this.disable();
		this.trigger('submit', [data]);
		cm.ajax('form', {view:this.getComponent()._getArray(), form:this._getArray(), actionName:actionName, data:data}, {
			success: function(response) {
				if (response.errors) {
					for (var i = response.errors.length-1, error; error = response.errors[i]; i--) {
						if (_.isArray(error)) {
							handler.getField(error[1]).error(error[0]);
						} else {
							handler.error(error);
						}
					}
					handler.trigger('error');
				}
	
				if (response.exec) {
					handler.evaluation = new Function(response.exec);
					handler.evaluation();
				}
	
				if (callbacks.success) {
					callbacks.success();
				}
	
				if (response.messages) {
					for (var i = 0, msg; msg = response.messages[i]; i++) {
						handler.message(msg);
					}
				}
	
				if (!response.errors) {
					handler.trigger('success success.' + actionName);
				}
			},
			complete: function() {
				handler.enable();
				handler.trigger('complete');
			}
		});
	},
	
	reset: function() {
		this.$().get(0).reset();
	},
	
	disable: function() {
		this.$().disable();
	},
	
	enable: function() {
		this.$().enable();
	},
	
	/**
	 * @param {String} message
	 */
	error: function(message) {
		cm.window.hint(message);
	},
	
	/**
	 * @param {String} message
	 */
	message: function(message) {
		cm.window.hint(message);
	}
});