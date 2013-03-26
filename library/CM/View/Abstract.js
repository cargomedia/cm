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
			return child.hasClass(className);
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
		if (parent.hasClass(className)) {
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
	 * @param {String} className
	 * @returns Boolean
	 */
	hasClass: function(className) {
		return _.contains(this.getClasses(), className);
	},

	/**
	 * @param {Boolean} [skipDomRemoval]
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
			this.$el.remove();
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
	 * @param {Object|Null} [options]
	 * @return jqXHR
	 */
	ajax: function(functionName, params, options) {
		options = _.defaults(options || {}, {
			'modal': false
		});
		params = params || {};
		var handler = this;

		if (options.modal) {
			var callbackComplete = options.complete;
			options.complete = function() {
				handler.enable();
				if (callbackComplete) {
					return callbackComplete(handler);
				}
			};
			this.disable();
		}

		var xhr = cm.ajax('ajax', {view: this._getArray(), method: functionName, params: params}, {
			success: function(response) {
				if (response.exec) {
					new Function(response.exec).call(handler);
				}
				if (options.success) {
					return options.success.call(handler, response.data);
				}
			},
			error: function(msg, type, isPublic) {
				if (options.error) {
					return options.error.call(handler, msg, type, isPublic);
				}
			},
			complete: function() {
				if (options.complete) {
					return options.complete.call(handler);
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
	 * @param {Object|Null} [options]
	 * @return jqXHR
	 */
	ajaxModal: function(functionName, params, options) {
		options = _.defaults(options || {}, {
			'modal': true
		});
		return this.ajax(functionName, params, options);
	},

	/**
	 * @param {String} className
	 * @param {Object|Null} [params]
	 * @param {Object|Null} [options]
	 * @return jqXHR
	 */
	loadComponent: function(className, params, options) {
		options = _.defaults(options || {}, {
			'success': function() {
				this.popOut();
			},
			'modal': true
		});
		params = params || {};
		params.className = className;
		var success = options.success;
		options.success = function(response) {
			this._injectView(response, success);
		};
		return this.ajax('loadComponent', params, options);
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
			if (response.redirectExternal) {
				cm.router.route(response.redirectExternal);
				return;
			}
			this._injectView(response, success);
		};
		return this.ajaxModal('loadPage', {path: path}, options);
	},

	/**
	 * @param {int} actionVerb
	 * @param {int} modelType
	 * @param {String} [channelKey]
	 * @param {Number} [channelType]
	 * @param {Function} callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
	 */
	bindAction: function(actionVerb, modelType, channelKey, channelType, callback) {
		if (!channelKey && !channelType) {
			if (!cm.options.stream.channel.key || !cm.options.stream.channel.type) {
				return;
			}
			channelKey = cm.options.stream.channel.key;
			channelType = cm.options.stream.channel.type;
		}
		var callbackResponse = function(response) {
			callback.call(this, response.action, response.model, response.data);
		};
		cm.action.bind(actionVerb, modelType, channelKey, channelType, callbackResponse, this);
		this.on('destruct', function() {
			cm.action.unbind(actionVerb, modelType, channelKey, channelType, callbackResponse, this);
		});
	},

	/**
	 * @param {String} channelKey
	 * @param {Number} channelType
	 * @param {String} event
	 * @param {Function} callback fn(array data)
	 */
	bindStream: function(channelKey, channelType, event, callback) {
		cm.stream.bind(channelKey, channelType, event, callback, this);
		this.on('destruct', function() {
			cm.stream.unbind(channelKey, channelType, event, callback, this);
		});
	},

	/**
	 * @param {String} channelKey
	 * @param {Number} channelType
	 * @param {String} [event]
	 * @param {Function} [callback]
	 */
	unbindStream: function(channelKey, channelType, event, callback) {
		cm.stream.unbind(channelKey, channelType, event, callback, this);
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
	 * @param {Function} callback
	 * @param {Integer} interval
	 * @return {Number}
	 */
	setInterval: function(callback, interval) {
		var self = this;
		var id = window.setInterval(function() {
			callback.call(self);
		}, interval);
		this.on('destruct', function() {
			window.clearInterval(id);
		});
		return id;
	},

	/**
	 * @param {Function} callback
	 * @param {Integer} timeout
	 * @return {Number}
	 */
	setTimeout: function(callback, timeout) {
		var self = this;
		var id = window.setTimeout(function() {
			callback.call(self);
		}, timeout);
		this.on('destruct', function() {
			window.clearTimeout(id);
		});
		return id;
	},

	/**
	 * @param {Function} fn
	 * @return {String}
	 */
	createGlobalFunction: function(fn) {
		var self = this;
		var functionName = 'cm_global_' + cm.getUuid().replace(/-/g, '_');
		window[functionName] = function() {
			fn.apply(self, arguments);
		};
		this.on('destruct', function() {
			delete window[functionName];
		});
		return functionName;
	},

	/**
	 * @param {String} mp3Path
	 * @param {Object} [params]
	 * @return {MediaElement}
	 */
	createAudioPlayer: function(mp3Path, params) {
		params = _.extend({loop: false, autoplay: false}, params);

		var $element = $('<audio />');
		$element.wrap('<div />');	// MediaElement needs a parent to show error msgs
		$element.attr('src', cm.getUrlResource('layout', 'audio/' + mp3Path));
		$element.attr('autoplay', params.autoplay);
		$element.attr('loop', params.loop);

		return new MediaElement($element.get(0), {
			startVolume: 1,
			flashName: cm.getUrlResource('layout', 'swf/flashmediaelement.swf'),
			silverlightName: cm.getUrlResource('layout', 'swf/silverlightmediaelement.xap'),
			error: function() {
				this.play = new Function();
				this.pause = new Function();
			}
		});
	},

	/**
	 * @param {jQuery} $element
	 * @param {String} url
	 * @param {Object} [flashvars]
	 * @param {Object} [flashparams]
	 * @param {Function} [callbackSuccess]
	 * @param {Function} [callbackFailure]
	 */
	createFlash: function($element, url, flashvars, flashparams, callbackSuccess, callbackFailure) {
		var eventCallbackName = this.createGlobalFunction(function(event) {
			event = JSON.parse(event);
			this.trigger(event.type, event.data);
		});
		flashvars = _.extend({'debug': cm.options.debug, 'eventCallback': eventCallbackName}, flashvars);
		flashparams = _.extend({'allowscriptaccess': 'sameDomain', 'allowfullscreen': 'true'}, flashparams);
		callbackSuccess = callbackSuccess || new Function();
		callbackFailure = callbackFailure || new Function();
		var id = $element.attr('id');
		if (!id) {
			id = 'swf-' + cm.getUuid();
			$element.attr('id', id);
		}
		var idSwf = id + '-object', attributes = {
			id: idSwf,
			name: idSwf,
			styleclass: 'embeddedWrapper-object'
		};

		var self = this;
		swfobject.embedSWF(url, id, "100%", "100%", "11.0.0", cm.getUrlResource('layout', 'swf/expressInstall.swf'), flashvars, flashparams, attributes, function(event) {
			if (event.success) {
				callbackSuccess.call(self, event.ref);
			} else {
				$element.html('<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>');
				callbackFailure.call(self);
			}
		});
	},

	/**
	 * @param {String} key
	 * @param {*} value
	 */
	storageSet: function(key, value) {
		cm.storage.set(this.getClass() + ':' + key, value);
	},

	/**
	 * @param {String} key
	 * @return *
	 */
	storageGet: function(key) {
		return cm.storage.get(this.getClass() + ':' + key);
	},

	/**
	 * @param {String} key
	 */
	storageDelete: function(key) {
		cm.storage.del(this.getClass() + ':' + key);
	},

	/**
	 * @param {String} key
	 * @param {Function} getter
	 * @return {*}
	 */
	cacheGet: function(key, getter) {
		return cm.cache.get(this.getClass() + ':' + key, getter, this);
	},

	/**
	 * @param {String} name
	 * @param {Object} variables
	 * @return {jQuery}
	 */
	renderTemplate: function(name, variables) {
		var template = this.cacheGet('template-' + name, function() {
			var $template = this.$('> script[type="text/template"].' + name);
			if (!$template.length) {
				cm.error.triggerThrow('Template `' + name + '` does not exist in `' + this.getClass() + '`');
			}
			return $template.html();
		});
		return cm.template.render(template, variables);
	},

	/**
	 * @param {Object} actions
	 * @param {String} [channelKey]
	 * @param {Number} [channelType]
	 */
	_bindActions: function(actions, channelKey, channelType) {
		_.each(actions, function(callback, key) {
			var match = key.match(/^(\S+)\s+(.+)$/);
			var modelType = cm.model.types[match[1]];
			var actionNames = match[2].split(/\s*,\s*/);
			_.each(actionNames, function(actionName) {
				var actionVerb = cm.action.verbs[actionName];
				this.bindAction(actionVerb, modelType, channelKey, channelType, callback);
			}, this);
		}, this);
	},

	/**
	 * @param {Object} streams
	 */
	_bindStreams: function(streams) {
		if (!cm.options.stream.channel) {
			return;
		}
		_.each(streams, function(callback, key) {
			this.bindStream(cm.options.stream.channel.key, cm.options.stream.channel.type, this.getClass() + ':' + key, callback);
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
