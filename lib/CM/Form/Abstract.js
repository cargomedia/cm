_fields: {},

initialize: function() {
	this._fields = {};
	_.each(this.options.fields, function(fieldInfo, name) {
		// Lazy construct
		var $field = this.$("#"+name);
		if ($field.length) {
			var fieldClass = window[fieldInfo.className];
			this._fields[name] = new fieldClass({"el": $field, "form": this, "name": name, "options": fieldInfo.options});
		}
	}, this);
	_.each(this._fields, function(field, name) {
		field.ready();
	}, this);
	
	var handler = this;
	
	_.each(this.options.actions, function(action, name) {
		var $btn = $('#'+this.getAutoId()+'-'+name+'-button');
		$btn.bind('click', {action: name}, function(event) {
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
	
	this.getComponent().registerForm(this);
},


ready: function() {
},


_ready: function() {
	this.ready();
	this.trigger('ready');
},

/**
 * @return string
 */
getAutoId: function() {
	return this.options.autoId;
},

/**
 * @return CM_Component
 */
getComponent: function() {
	return this.options.component;
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
		return $(this.el);
	}
	selector = selector.replace('#', '#'+this.getAutoId()+'-');
	return $(selector, this.el);
},

collectData: function(action_name) {
	var form_data = this.$().serializeArray();
	var action = this.options.actions[action_name];

	var data = {};
	var regex = /^([\w\-]+)(\[([^\]]+)?\])?(\[([^\]]+)?\])?/i;
	var name, match, key;

	for (var i = 0, item; item = form_data[i]; i++) {
		// parsing html name
		match = regex.exec(item.name);
		name = match[1];
		item.value = item.value || '';

		if (typeof action.fields[name] == 'undefined') {
			continue;
		}

		if (match[2]) {
			if (match[2] == '[]') {
				if (data[name] === undefined) {
					data[name] = [];
				}
				data[name].push(item.value);
			} else if (match[3]) {
				key = match[3];
				if (typeof data[name] == 'undefined') {
					data[name] = {length: 0};
				}

				data[name].length++;

				//second brackets
				if (match[4] == '[]') {
					if (data[name][key] === undefined) {
						data[name][key] = [];
					}
					data[name][key].push(item.value);
				} else if (match[5]) {
					var sub_key = match[5];
					if (typeof data[name][key] == 'undefined') {
						data[name][key] = {length: 0};
					}
					data[name][key][sub_key] = item.value;
				} else {
					data[name][key] = item.value;
				}

			}
		} else { // if there are no brackets
			data[name] = item.value;
		}
	}

	var errors = [];
	var field, required;

	var focusSet = false;
	for (key in action.fields) {
		required = action.fields[key];
		field = this._fields[key];

		if ( data[key] && (data[key].length !== 0) ) {
			try {
				field.validate(data[key], required);
			} catch (e) {
				var err_msg = sk.language.get('%forms._errors.illegal_value');
				if (e.message) {
					err_msg = e.message;
				}
				errors.push({msg: err_msg, key: key});
			}
		} else if (required) {
			var err_msg = 'Required';
			var $labels = $('label[for="' +this.getAutoId()+ '-' +key+ '-input"]');
			if ($labels.length) {
				err_msg = sk.language.get('%forms._errors.required', {label:$labels.eq(0).text()});
			}
			errors.push({msg: err_msg, key: key});
		}

		if (data[key] && data[key].length !== undefined) {
			delete(data[key]['length']);
		}
	}

	if (errors.length) {
		for (var i = errors.length-1, err; err = errors[i]; i--) {
			this.error(err.msg, err.key);
		}
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
			sk.ui.confirm(sk.language.get(this.options.actions[action_name].confirm_msg), function() {
				handler.submit(action_name, true, data);
			});
			return false;
		}
		
		this.disable()
		this.trigger('submit', [data]);
		sk.ajax('form', {component:this.getComponent().getArray(), className:this._class, actionName:action_name, data:data}, {
			success: function(response) {
				handler.enable();
				if (response.errors) {
					for (var i = response.errors.length-1, error; error = response.errors[i]; i--) {
						if (error.constructor == Array) {
							handler.error(error[0], error[1]);
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
			error: function(msg, name) {
				handler.enable();
			},
			complete: function() {
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

error: function(message, field_key) {
	var field = field_key ? this.getField(field_key) : null;
	if (field) {
		var $container = field.$('.messages');
		$container.html("");

		if (message) {
			$container.append('<div class="form_field_error" style="display:none"></div><br clear="all" />')
			.children('.form_field_error').html(message).fadeIn('fast');
			
			this.getField(field_key).$('input, select, textarea').focus();
		}
	} else {
		sk.window.hint.error(message);
	}
},

message: function(message) {
	sk.window.hint.message(message);
}
