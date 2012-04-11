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
			this._fields[name] = new fieldClass({"el": $field, "parent": this, "name": name, "options": fieldInfo.options});
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

collectData: function(action_name) {
	var form_data = this.$().serializeArray();
	var action = this.options.actions[action_name];

	var data = {};
	var regex = /^([\w\-]+)(\[([^\]]+)?\])?$/;
	var name, match;

	for (var i = 0, item; item = form_data[i]; i++) {
		match = regex.exec(item.name);
		name = match[1];
		item.value = item.value || '';

		if (typeof action.fields[name] == 'undefined') {
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

	var hasErrors = false
	for (var fieldName in action.fields) {
		var required = action.fields[fieldName];
		if (required && _.isEmpty(data[fieldName])) {
			var field = this.getField(fieldName);
			var errorMessage = 'Required';
			var $labels = $('label[for="' + field.getAutoId() + '-input"]');
			if ($labels.length) {
				errorMessage = cm.language.get('%forms._errors.required', {label:$labels.first().text()});
			}
			field.error(errorMessage);
			hasErrors = true;
		}
	}
	if (hasErrors) {
		return false;
	}

	return data;
},

submit: function(action_name, confirmed, data, callbacks) {
	confirmed = confirmed || false;
	callbacks = callbacks || {};
	action_name = action_name || this.options.default_action;
	
	if (!confirmed) {
		$('.form_field_error', this.$())
			.next('br').remove()
			.andSelf().remove();
	}

	data = data || this.collectData(action_name);

	if (data) {
		var handler = this;
		if (this.options.actions[action_name].confirm_msg && !confirmed) {
			cm.ui.confirm(cm.language.get(this.options.actions[action_name].confirm_msg), function() {
				handler.submit(action_name, true, data);
			});
			return false;
		}
		
		this.disable()
		this.trigger('submit', [data]);
		cm.ajax('form', {view:this.getComponent()._getArray(), form:this._getArray(), actionName:action_name, data:data}, {
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
					callbacks.success(response.data);
				}
				
				if (response.messages) {
					for (var i = 0, msg; msg = response.messages[i]; i++) {
						handler.message(msg);
					}
				}

				if (!response.errors) {
					handler.trigger('success', [response.data]);
				}
			},
			complete: function() {
				handler.enable();
				handler.trigger('complete');
			}
		});
	}
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
 * @param string message
 */
error: function(message) {
	cm.window.hint(message);
},

/**
 * @param string message
 */
message: function(message) {
	cm.window.hint(message);
}
