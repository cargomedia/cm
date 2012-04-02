_children: [],

initialize: function() {
	this._children = [];

	if (this.getParent()) {
		this.getParent().registerChild(this);
	}

	if (this.actions) {
		this._bindActions(this.actions);
	}
	if (this.streams) {
		this._bindStreams(this.streams);
	}
},

ready: function() {
},

_ready: function() {
	this.ready();
	_.each(this.getChildren(), function(child) {
		child._ready();
	});
},

/**
 * @param jQuery $dom
 */
initDom: function($dom) {
	$dom.placeholder();
	$dom.find('.timeago').timeago();
	$dom.find('button[title]').qtip({
		position: {my: 'bottom center', at: 'top center'},
		style: {classes: 'ui-tooltip-tipped'}
	});
},

/**
 * @param CM_View_Abstract child
 */
registerChild: function(child) {
	this._children.push(child);
},

/**
 * @return CM_View_Abstract[]
 */
getChildren: function() {
	return this._children;
},

/**
 * @param string className
 * @return CM_View_Abstract|null
 */
findChild: function(className){
	return _.find(this.getChildren(), function(child) {
		return child._class == className;
	}) || null;
},

/**
 * @return CM_View_Abstract|null
 */
getParent: function() {
	if (this.options.parent) {
		return this.options.parent;
	}
	return null;
},

/**
 * @return string
 */
getAutoId: function() {
	return this.el.id;
},

/**
 * @return object
 */
getParams: function() {
	return this.options.params || {};
},

/**
 * @param bool skipDomRemoval OPTIONAL
 */
remove: function(skipDomRemoval) {
	this.trigger("destruct");

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

	delete cm.views[this.getAutoId()];

	if (!skipDomRemoval) {
		this.$().remove();
	}
},

disable: function() {
	this.$().disable();
},

enable: function() {
	this.$().enable();
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
	var xhr = cm.ajax('ajax', {view:this._getArray(), method:functionName, params:params}, {
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
 * @param string functionName
 * @param array|null params
 * @param object|null callbacks
 * @return jqXHR
 */
ajaxModal: function(functionName, params, callbacks) {
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
	this.ajax(functionName, params, callbacks);
},

loadComponent: function(className, params, options) {
	options = options || {};
	params = params || {};
	params.className = className;
	var successPopOut = options.successPopOut || function() {};
	var successPre = options.success ? options.success :  function() { this.popOut(); };
	var successPost = options.success ? function() {} : function() { successPopOut.call(this); }
	options.success = function(autoId) {
		var handlerNew = cm.views[autoId];
		successPre.call(handlerNew);
		handlerNew._ready();
		successPost.call(handlerNew);
	};
	return this.ajaxModal('loadComponent', params, options);
},

/**
 * @param int actionTypes
 * @param int modelType
 * @param function callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
 */
bindAction: function(actionType, modelType, callback) {
	cm.action.bind(actionType, modelType, callback, this);
	this.on('destruct', function() {
		cm.action.unbind(actionType, modelType, callback, this);
	});
},

/**
 * @param string event
 * @param function callback fn(array data)
 */
bindStream: function(event, callback) {
	var namespace = this._class + ':' + event;
	cm.stream.bind(namespace, callback, this);
	this.on('destruct', function() {
		cm.stream.unbind(namespace, callback, this);
	});
},

/**
 * @param object
 */
_bindActions: function(actions) {
	for (key in actions) {
		var callback = actions[key];
		var match = key.match(/^(\S+)\s+(.+)$/);
		var modelType = cm.model.types[match[1]];
		var actionNames = match[2].split(/\s*,\s*/);
		_.each(actionNames, function(actionName) {
			var actionType = cm.action.types[actionName];
			this.bindAction(actionType, modelType, callback);
		}, this);
	}
},

/**
 * @param object
 */
_bindStreams: function(streams) {
	for (key in streams) {
		var callback = streams[key];
		this.bindStream(key, callback);
	}
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
