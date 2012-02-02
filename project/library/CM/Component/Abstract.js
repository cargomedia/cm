_children: [],
_forms: [],

initialize: function() {
	this._children = [];
	this._forms = [];
	
	if (this.getParent()) {
		this.getParent().registerChild(this);
	}
},

/**
 * Called when all components are loaded
 */
ready: function() {
},

_ready: function() {
	this.ready();

	this.$(".timeago").timeago();
	this.$().placeholder();
	this.$('button[title]').qtip({
		position: {my: 'bottom center', at: 'top center'},
		style: {classes: 'ui-tooltip-tipped'}
	});

	_.each(this.getForms(), function(form) {
		form.ready();
	});
	_.each(this.getChildren(), function(child) {
		child._ready();
	});
},

/**
 * Called on popOut()
 */
repaint: function() {
},

/**
 * @return string
 */
getAutoId: function() {
	return this.options.autoId;
},

/**
 * @return object
 */
getParams: function() {
	return this.options.params;
},

/**
 * @return CM_Component_Abstract[]
 */
getChildren: function() {
	return this._children;
},

/**
 * @param string className
 * @return CM_Component_Abstract|null
 */
findChild: function(className){
	return _.find(this.getChildren(), function(child) {
		return child._class == className;
	}) || null;
},

/**
 * @param CM_Component_Abstract child
 */
registerChild: function(child) {
	this._children.push(child);
},

/**
 * @return CM_Component_Abstract|null
 */
getParent: function() {
	if (this.options.parent) {
		return this.options.parent;
	}
	return null;
},

/**
 * @return CM_Form_Abstract[]
 */
getForms: function() {
	return this._forms;
},

/**
 * @param string name
 * @return CM_Form_Abstract|null
 */
findForm: function(name) {
	return _.find(this.getForms(), function(form) {
		return form._class == name;
	}) || null;
},

/**
 * @param CM_Form_Abstract form
 */
registerForm: function(form) {
	this._forms.push(form);
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

disable: function() {
	this.$().disable();
},

enable: function() {
	this.$().enable();
},

popOut: function(options) {
	this.$().floatOut(options);
	this.repaint();
},

popIn: function() {
	this.$().floatIn();
},

/**
 * @param string message
 */
error: function(message) {
	cm.window.hint.error(message);
},

/**
 * @param string message
 */
message: function(message) {
	cm.window.hint.message(message);
},

/**
 * @param function callback fn(array data)
 */
bindStream: function(callback) {
	var namespace = this._class;
	cm.stream.bind(namespace, callback);
	this.on('destruct', function() {
		cm.stream.unbind(namespace, callback);
	});
},

/**
 * @param int actionTypes
 * @param int modelType
 * @param function callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
 */
bindAction: function(actionType, modelType, callback) {
	cm.action.bind(actionType, modelType, callback);
	this.on('destruct', function() {
		cm.action.unbind(actionType, modelType, callback);
	});
},

/**
 * @return object
 */
_getArray: function() {
	return {
		className: this._class,
		id: this.getAutoId(),
		params: this.getParams(),
		parentId: this.getParent() ? this.getParent().getAutoId() : null
	};
},

/**
 * @param string functionName
 * @param array|null params
 * @param object|null callbacks
 * @param bool|null cache
 * @return jqXHR
 */
ajax: function(functionName, params, callbacks, cache) {
	callbacks = callbacks || {};
	params = params || {};
	var handler = this;
	var xhr = cm.ajax('ajax', {component:this._getArray(), method:functionName, params:params}, {
		success: function(response) {
			if (response.exec) {
				var exec = new Function(response.exec);
				exec.call(handler);
			}
			if (callbacks.success) {
				return callbacks.success.call(handler, response.data);
			}
		},
		error: function(msg, type) {
			if (callbacks.error) {
				return callbacks.error.call(handler, msg, type);
			}
		},
		complete: function() {
			if (callbacks.complete) {
				return callbacks.complete.call(handler);
			}
		}
	}, cache);
	this.on('destruct', function() {
		xhr.abort();
	});
	return xhr;
},

/**
 * @return jqXHR
 */
ajaxModal: function(apply_func, params, callbacks) {
	callbacks = callbacks || {};
	var handler = this;
	var callbackComplete = callbacks.complete;
	callbacks.complete = function() {
		handler.enable();
		if (callbackComplete) {
			return callbackComplete(handler);
		}
	};
	this.disable();
	this.ajax(apply_func, params, callbacks);
},

load: function(className, params, options) {
	var handler = this;
	options = options || {};
	params = params || {};
	params.component = className;
	var successPopOut = options.successPopOut || function() {};
	var successPre = options.success ? options.success :  function() { this.popOut(); };
	var successPost = options.success ? function() {} : function() { successPopOut.call(this); }
	options.success = function(componentId) {
		var handlerNew = cm.components[componentId];
		successPre.call(handlerNew);
		handlerNew._ready();
		successPost.call(handlerNew);
	};
	return this.ajaxModal('load', params, options);
},

/**
 * @return XMLHttpRequest
 */
reload: function(params) {
	return this.ajaxModal('reload', params);
},

remove: function(skipDomRemoval) {
	if (this.getParent()) {
		var siblings = this.getParent().getChildren();
		for (var i = 0, sibling; sibling = siblings[i]; i++) {
			if (sibling.getAutoId() == this.getAutoId()) {
				siblings.splice(i, 1);
			}
		}
	}

	_.each(this.getChildren(), function(child) {
		child.remove();
	});

	_.each(this.getForms(), function(form) {
		delete cm.forms[form.getAutoId()];
	});
	this.trigger("destruct");
	delete cm.components[this.getAutoId()];


	if (!skipDomRemoval) {
		this.$().remove();
	}
},

/**
 * @param string key
 * @param mixed key
 */
storageSet: function(key, value) {
	cm.storage.set(this._class + '_' + key, value);
},

/**
 * @param string key
 * @return mixed
 */
storageGet: function(key) {
	return cm.storage.get(this._class + '_' + key);
},

/**
 * @param string key
 */
storageDelete: function(key) {
	cm.storage.del(this._class + '_' + key);
}
