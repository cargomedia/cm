_ready: function() {
	this.$(".timeago").timeago();
	this.$().placeholder();
	this.$('button[title]').qtip({
		position: {my: 'bottom center', at: 'top center'},
		style: {classes: 'ui-tooltip-tipped'}
	});

	this.ready();
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
 * @return object
 */
getParams: function() {
	return this.options.params;
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
	cm.window.hint(message);
},

/**
 * @param string message
 */
message: function(message) {
	cm.window.hint(message);
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
	options.success = function(autoId) {
		var handlerNew = cm.views[autoId];
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
}