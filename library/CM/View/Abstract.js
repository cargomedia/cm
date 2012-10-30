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
		this.events = this.collectEvents();

		if (this.actions) {
			this._bindActions(this.actions);
		}
		if (this.streams) {
			this._bindStreams(this.streams);
		}
		if (this.childrenEvents) {
			this._bindChildrenEvents(this.childrenEvents);
		}
		this.on('all', function(eventName, data) {
			cm.viewEvents.trigger(this, eventName, data);
		});
	},

	collectEvents: function() {
		var eventsObjects = [], currentConstructor = this.constructor, currentProto = currentConstructor.prototype;

		do {
			if (currentProto.hasOwnProperty('events')) {
				eventsObjects.unshift(currentProto.events);
			}
		} while (currentConstructor = ( currentProto = currentConstructor.__super__ ) && currentProto.constructor);
		eventsObjects.unshift({});
		return _.extend.apply(_, eventsObjects);
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
		child.options.parent = this;
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

	/**
	 * @param {CM_View_Abstract} view
	 */
	replaceWith: function(view) {
		view._callbacks = this._callbacks;
		this.getParent().registerChild(view);
		this.$().replaceWith(view.$());
		this.remove(true);
	},

	disable: function() {
		this.$().disable();
	},

	enable: function() {
		this.$().enable();
	},

	/**
	 * @param {String} functionName
	 * @param {Object|Null} [params]
	 * @param {Object|Null} [callbacks]
	 * @return jqXHR
	 */
	ajax: function(functionName, params, callbacks) {
		callbacks = callbacks || {};
		params = params || {};
		var handler = this;
		var xhr = cm.ajax('ajax', {view: this._getArray(), method: functionName, params: params}, {
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
	 * @param {Object|Null} [params]
	 * @param {Object|Null} [callbacks]
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
	 * @param {Object|Null} [params]
	 * @param {Object|Null} [options]
	 * @return jqXHR
	 */
	loadComponent: function(className, params, options) {
		options = options || {};
		params = params || {};
		params.className = className;
		var success = options.success ? options.success : function() {
			this.popOut();
		};
		options.success = function(response) {
			this._injectView(response, success);
		};
		return this.ajaxModal('loadComponent', params, options);
	},

	/**
	 * @param {String} path
	 * @param {Object} [options]
	 * @return jqXHR
	 */
	loadPage: function(path, options) {
		options = options || {};
		var success = options.success;
		options.success = function(response) {
			this._injectView(response, success);
		};
		return this.ajaxModal('loadPage', {path: path}, options);
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
	 * @param {String} viewClassName
	 * @param {String} event
	 * @param {Function} callback fn(CM_View_Abstract view, array data)
	 */
	bindChildrenEvent: function(viewClassName, event, callback) {
		cm.viewEvents.bind(this, viewClassName, event, callback, this);
		this.on('destruct', function() {
			cm.viewEvents.unbind(this, viewClassName, event, callback, this);
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
	 * @param {String} key
	 * @param {*} value
	 */
	storageSet: function(key, value) {
		cm.storage.set(this.getClass() + '_' + key, value);
	},

	/**
	 * @param {String} key
	 * @return *
	 */
	storageGet: function(key) {
		return cm.storage.get(this.getClass() + '_' + key);
	},

	/**
	 * @param {String} key
	 */
	storageDelete: function(key) {
		cm.storage.del(this.getClass() + '_' + key);
	},

	/**
	 * @param {String} key
	 * @param {Function} getter
	 * @return {*}
	 */
	cacheGet: function(key, getter) {
		return cm.cache.get(this.getClass() + '_' + key, getter, this);
	},

	/**
	 * @param {String} name
	 * @param {Object} variables
	 * @return {jQuery}
	 */
	renderTemplate: function (name, variables) {
		var template = this.cacheGet('name', function () {
			var $template = this.$('script[type="text/html"].' + name);
			if (!$template.length) {
				cm.error.triggerThrow('Template `' + name + '` does not exist in `' + this.getClass() + '`');
			}
			return $template.html();
		});
		return cm.template.render(template, variables);
	},

	/**
	 * @param {Object} actions
	 */
	_bindActions: function(actions) {
		_.each(actions, function(callback, key) {
			var match = key.match(/^(\S+)\s+(.+)$/);
			var modelType = cm.model.types[match[1]];
			var actionNames = match[2].split(/\s*,\s*/);
			_.each(actionNames, function(actionName) {
				var actionVerb = cm.action.verbs[actionName];
				this.bindAction(actionVerb, modelType, callback);
			}, this);
		}, this);
	},

	/**
	 * @param {Object} streams
	 */
	_bindStreams: function(streams) {
		_.each(streams, function(callback, key) {
			this.bindStream(key, callback);
		}, this);
	},

	/**
	 * @param {Object} events
	 */
	_bindChildrenEvents: function(events) {
		_.each(events, function(callback, key) {
			var match = key.match(/^(\S+)\s+(.+)$/);
			var viewName = match[1];
			var eventNames = match[2].split(/\s*,\s*/);
			_.each(eventNames, function(eventName) {
				this.bindChildrenEvent(viewName, eventName, callback);
			}, this);
		}, this);
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
	},

	/**
	 * @param {Object} response
	 * @param {Function} [successPre]
	 * @private
	 */
	_injectView: function(response, successPre) {
		cm.window.appendHidden(response.html);
		new Function(response.js).call(this);
		var view = cm.views[response.autoId];
		this.registerChild(view);
		if (successPre) {
			successPre.call(view, response);
		}
		view._ready();
		return view;
	}
});