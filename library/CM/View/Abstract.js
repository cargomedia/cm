/**
 * @class CM_View_Abstract
 * @extends Backbone.View
 */
var CM_View_Abstract = Backbone.View.extend({
	_class: 'CM_View_Abstract',

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
	 * @param {CM_View_Abstract} child
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
	 * @param {String} className
	 * @return CM_View_Abstract|null
	 */
	findChild: function(className) {
		return _.find(this.getChildren(), function(child) {
			return _.contains(child.getClasses(), className);
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
	 * @param {String} className
	 * @return CM_View_Abstract|null
	 */
	findParent: function(className) {
		var parent = this.getParent();
		if (!parent) {
			return null;
		}
		if (_.contains(parent.getClasses(), className)) {
			return parent;
		}
		return parent.findParent(className);
	},

	/**
	 * @return String
	 */
	getAutoId: function() {
		return this.el.id;
	},

	/**
	 * @return Object
	 */
	getParams: function() {
		return this.options.params || {};
	},

	/**
	 * @return string[]
	 */
	getClasses: function() {
		var classes = [this.getClass()];
		if ('CM_View_Abstract' != this.getClass()) {
			classes = classes.concat(this.constructor.__super__.getClasses());
		}
		return classes;
	},

	/**
	 * @return String
	 */
	getClass: function() {
		return this._class;
	},

	/**
	 * @param {Boolean} skipDomRemoval OPTIONAL
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

		_.each(_.clone(this.getChildren()), function(child) {
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
	 * @param {String} functionName
	 * @param {Array|Null} params
	 * @param {Object|Null} callbacks
	 * @return jqXHR
	 */
	ajax: function(functionName, params, callbacks) {
		callbacks = callbacks || {};
		params = params || {};
		var handler = this;
		var xhr = cm.ajax('ajax', {view:this._getArray(), method:functionName, params:params}, {
			success: function(response) {
				if (response.exec) {
					new Function(response.exec).call(handler);
				}
				if (callbacks.success) {
					return callbacks.success.call(handler, response.data);
				}
			},
			error: function(msg, type, isPublic) {
				if (callbacks.error) {
					return callbacks.error.call(handler, msg, type, isPublic);
				}
			},
			complete: function() {
				if (callbacks.complete) {
					return callbacks.complete.call(handler);
				}
			}
		});
		this.on('destruct', function() {
			xhr.abort();
		});
		return xhr;
	},

	/**
	 * @param {String} functionName
	 * @param {Array|Null} params
	 * @param {Object|Null} callbacks
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
		return this.ajax(functionName, params, callbacks);
	},

	/**
	 * @param {String} className
	 * @param {Object|Null} params
	 * @param {Object|Null} options
	 * @return jqXHR
	 */
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
	 * @param {String} path
	 * @param {Object|Null} callbacks
	 * @return {jqXHR}
	 */
	loadPage: function (path, callbacks) {
		callbacks = callbacks || {};
		var success = callbacks.success || function() {};

		return this.ajaxModal('loadPage', {path: path}, {
			success: function(response) {
				cm.window.appendHidden(response.html);
				new Function(response.js).call(this);
				var page = cm.views[response.autoId];
				success.call(page, response.title, response.url, response.menuEntryHashes, response.layoutClass);
				page._ready();
			},
			error: callbacks.error,
			complete: callbacks.complete
		});
	},

	/**
	 * @param {int} actionVerb
	 * @param {int} modelType
	 * @param {Function} callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
	 */
	bindAction: function(actionVerb, modelType, callback) {
		cm.action.bind(actionVerb, modelType, callback, this);
		this.on('destruct', function() {
			cm.action.unbind(actionVerb, modelType, callback, this);
		});
	},

	/**
	 * @param {String} event
	 * @param {Function} callback fn(array data)
	 */
	bindStream: function(event, callback) {
		var namespace = this.getClass() + ':' + event;
		cm.stream.bind(namespace, callback, this);
		this.on('destruct', function() {
			cm.stream.unbind(namespace, callback, this);
		});
	},

	/**
	 * @param {String|Function} callback
	 * @param {int} interval
	 * @return {int}
	 */
	setInterval: function(callback, interval) {
		var id = window.setInterval(callback, interval);
		this.on('destruct', function() {
			window.clearInterval(id);
		});
		return id;
	},

	/**
	 * @param {String|Function} callback
	 * @param {int} timeout
	 * @return {int}
	 */
	setTimeout: function(callback, timeout) {
		var id = window.setTimeout(callback, timeout);
		this.on('destruct', function() {
			window.clearTimeout(id);
		});
		return id;
	},

	/**
	 * @param {Object}
	 */
	_bindActions: function(actions) {
		for (key in actions) {
			var callback = actions[key];
			var match = key.match(/^(\S+)\s+(.+)$/);
			var modelType = cm.model.types[match[1]];
			var actionNames = match[2].split(/\s*,\s*/);
			_.each(actionNames, function(actionName) {
				var actionVerb = cm.action.verbs[actionName];
				this.bindAction(actionVerb, modelType, callback);
			}, this);
		}
	},

	/**
	 * @param {Object}
	 */
	_bindStreams: function(streams) {
		for (key in streams) {
			var callback = streams[key];
			this.bindStream(key, callback);
		}
	},

	/**
	 * @return Object
	 */
	_getArray: function() {
		return {
			className: this.getClass(),
			id: this.getAutoId(),
			params: this.getParams(),
			parentId: this.getParent() ? this.getParent().getAutoId() : null
		};
	}
});