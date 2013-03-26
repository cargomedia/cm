/**
 * @class CM_App
 * @extends CM_Class_Abstract
 */
var CM_App = CM_Class_Abstract.extend({
	/** @type Object **/
	views: {},

	/** @type {Object|Null} **/
	viewer: null,

	/** @type Object **/
	options: {},

	ready: function() {
		this.dom.ready();
		this.window.ready();
		this.date.ready();
		this.template.ready();
	},

	/**
	 * @param {String|Null} [className]
	 * @return {CM_Component_Abstract|Null}
	 */
	findView: function(className) {
		if (!className) {
			var view = _.find(cm.views, function(view) {
				return !view.getParent();
			});
			if (!view) {
				cm.error.trigger('Cannot find root component');
			}
			return view;
		}
		return _.find(this.views, function(view) {
			return view.hasClass(className);
		}) || null;
	},

	/**
	 * @param {String|Null} [className]
	 * @return CM_Component_Abstract[]
	 */
	findViewList: function(className) {
		if (!className) {
			return this.views;
		}
		return _.filter(this.views, function(view) {
			return view.hasClass(className);
		});
	},

	/**
	 * @param {Number} min
	 * @param {Number} max
	 * @return {Number}
	 */
	rand: function(min, max) {
		return min + Math.floor(Math.random() * (max - min + 1));
	},

	/**
	 * Source: http://stackoverflow.com/a/2117523
	 * @return {String}
	 */
	getUuid: function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	},

	/**
	 * @param {Number} delayMin
	 * @param {Number} delayMax
	 * @param {Function} execution fn({Function} retry, {Function} resetDelay)
	 */
	retryDelayed: function(delayMin, delayMax, execution) {
		delayMin *= 1000;
		delayMax *= 1000;
		var delay = delayMin;
		var timeout;
		var resetDelay = function() {
			delay = delayMin;
			window.clearTimeout(timeout);
		};
		var retry = function() {
			var self = this;
			window.clearTimeout(timeout);
			timeout = window.setTimeout(function() {
				execution.call(self, retry, resetDelay);
				delay = Math.min(Math.max(delayMin, delay * 2), delayMax);
			}, delay);
		};
		execution.call(this, retry, resetDelay);
	},

	/**
	 * @param {String} [path]
	 * @param {Array} [params]
	 * @return {String}
	 */
	getUrl: function(path, params) {
		path = path || '';
		params = params || null;
		if (params) {
			path += '?' + jQuery.param(params, true);
		}
		return cm.options.url + path;
	},

	/**
	 * @param {String} path
	 * @return {String}
	 */
	getUrlStatic: function(path) {
		var url = cm.options.urlStatic;
		if (path) {
			url += path + '?' + cm.options.releaseStamp;
		}
		return url;
	},

	/**
	 * @param {String} type
	 * @param {String} path
	 * @return {String}
	 */
	getUrlResource: function(type, path) {
		var urlPath = '';
		if (type && path) {
			urlPath += '/' + type;
			if (cm.options.language) {
				urlPath += '/' + cm.options.language.abbreviation;
			}
			urlPath += '/' + cm.options.siteId + '/' + cm.options.releaseStamp + '/' + path;
		}
		return cm.options.urlResource + urlPath;
	},

	/**
	 * @param {String} path
	 * @return {String}
	 */
	getUrlUserContent: function(path) {
		path = path || '';
		return cm.options.urlUserContent + '/' + path;
	},

	/**
	 * @param {String} type
	 * @return {String}
	 */
	getUrlAjax: function(type) {
		var path = '/' + type;
		if (cm.options.language) {
			path += '/' + cm.options.language.abbreviation;
		}
		path += '/' + this.options.siteId;
		return this.getUrl(path);
	},

	error: {
		_callbacks: {_all: []},
		/**
		 * @param {String} msg
		 * @param {String} [type]
		 * @param {Boolean} [isPublic]
		 */
		trigger: function(msg, type, isPublic) {
			for (var i = 0; i < this._callbacks._all.length; i++) {
				if (false === this._callbacks._all[i](msg, type, isPublic)) {
					return;
				}
			}
			if (this._callbacks[type]) {
				for (var j = 0; j < this._callbacks[type].length; j++) {
					if (false === this._callbacks[type][j](msg, type, isPublic)) {
						return;
					}
				}
			}
			if (isPublic) {
				cm.window.hint(msg);
			} else {
				if (type) {
					msg = type + ': ' + msg;
				}
				cm.window.hint(msg);
				if (window.console && console.error) {
					console.error('Error: ' + msg);
				}
			}
		},
		/**
		 * @param {String} msg
		 * @param {String} [type]
		 * @param {Boolean} [isPublic]
		 * @throws {String}
		 */
		triggerThrow: function(msg, type, isPublic) {
			this.trigger(msg, type, isPublic);
			throw msg;
		},
		/**
		 * @param {Function} callback fn(msg, type, isPublic)
		 */
		bind: function(callback) {
			this.bindType('_all', callback);
		},
		/**
		 * @param {String} type
		 * @param {Function} callback fn(msg, type, isPublic)
		 */
		bindType: function(type, callback) {
			if (!this._callbacks[type]) {
				this._callbacks[type] = [];
			}
			this._callbacks[type].push(callback);
		}
	},

	debug: {
		/**
		 * @param {CM_View_Abstract} [view]
		 * @param {Number} [indentation]
		 */
		viewTree: function(view, indentation) {
			view = view || cm.findView();
			indentation = indentation || 0;
			console.log(new Array(indentation + 1).join("  ") + view.getClass() + " (", view.el, ")");
			_.each(view.getChildren(), function(child) {
				cm.debug.viewTree(child, indentation + 1);
			});
		}
	},

	dom: {
		_swfId: 0,
		ready: function() {
			if (window.addEventListener) {
				window.addEventListener('load', function() {
					new FastClick(document.body);
				}, false);
			}
		},
		/**
		 * @param {jQuery} $dom
		 */
		setup: function($dom) {
			$dom.placeholder();
			$dom.find('.timeago').timeago();
			$dom.find('textarea.autosize, .autosize textarea').autosize();
			$dom.find('.clipSlide').clipSlide();
			$dom.find('button[title]').tooltip();
			$dom.find('.toggleNext').toggleNext();
			$dom.find('.tabs').tabs();
			$dom.find('.openx-ad').openx();
		}
	},

	string: {
		padLeft: function(str, length, character) {
			character = character || ' ';
			string = String(str);
			return new Array(length - string.length + 1).join(character) + string;
		}
	},

	date: {
		ready: function() {
			$.timeago.settings.allowFuture = true;
			$.timeago.settings.strings = {
				prefixAgo: cm.language.get('.date.timeago.prefixAgo', {count: '%d'}),
				prefixFromNow: cm.language.get('.date.timeago.prefixFromNow', {count: '%d'}),
				suffixAgo: cm.language.get('.date.timeago.suffixAgo', {count: '%d'}),
				suffixFromNow: cm.language.get('.date.timeago.suffixFromNow', {count: '%d'}),
				seconds: cm.language.get('.date.timeago.seconds', {count: '%d'}),
				minute: cm.language.get('.date.timeago.minute', {count: '%d'}),
				minutes: cm.language.get('.date.timeago.minutes', {count: '%d'}),
				hour: cm.language.get('.date.timeago.hour', {count: '%d'}),
				hours: cm.language.get('.date.timeago.hours', {count: '%d'}),
				day: cm.language.get('.date.timeago.day', {count: '%d'}),
				days: cm.language.get('.date.timeago.days', {count: '%d'}),
				month: cm.language.get('.date.timeago.month', {count: '%d'}),
				months: cm.language.get('.date.timeago.months', {count: '%d'}),
				year: cm.language.get('.date.timeago.year', {count: '%d'}),
				years: cm.language.get('.date.timeago.years', {count: '%d'}),
				wordSeparator: " ",
				numbers: []
			};
		},
		/**
		 * @return {Number} Unix-timestamp
		 */
		timestamp: function() {
			return (new Date()).getTime();
		},
		/**
		 * @param {Date} date
		 * @return {String}
		 */
		iso8601: function(date) {
			return date.getUTCFullYear() + '-' + cm.string.padLeft(date.getUTCMonth() + 1, 2, '0') + '-' + cm.string.padLeft(date.getUTCDate(), 2, '0') + 'T' + cm.string.padLeft(date.getUTCHours(), 2, '0') + ':' + cm.string.padLeft(date.getUTCMinutes(), 2, '0') + ':' + cm.string.padLeft(date.getUTCSeconds(), 2, '0') + '.' + cm.string.padLeft(date.getUTCMilliseconds(), 3, '0') + 'Z';
		},
		/**
		 * @param {Integer} [timestamp]
		 * @return {jQuery}
		 */
		$timeago: function(timestamp) {
			return $(this.timeago(timestamp)).timeago();
		},
		/**
		 * @param {Integer} [timestamp]
		 * @return {jQuery}
		 */
		timeago: function(timestamp) {
			var date;
			if (timestamp) {
				date = new Date(timestamp * 1000);
			} else {
				date = new Date();
			}
			var print = date.toLocaleString();
			var iso8601 = this.iso8601(date);
			return '<abbr class="timeago" title="' + iso8601 + '">' + print + '</abbr>';
		}
	},

	language: {
		_keys: {},

		/**
		 * @param {String} key
		 * @param {String} value
		 */
		set: function(key, value) {
			this._keys[key] = value;
		},

		/**
		 * @param {Object} translations
		 */
		setAll: function(translations) {
			this._keys = translations;
		},

		/**
		 * @param {String} key
		 * @param {Object} [variables]
		 */
		get: function(key, variables) {
			if (this._keys[key] === undefined) {
				cm.rpc('CM_Model_Language.requestTranslationJs', {languageKey: key});
				this.set(key, key);
			}
			var value = this._keys[key];
			if (variables) {
				_.each(variables, function(variableValue, variableKey) {
					value = value.replace('{$' + variableKey + '}', variableValue);
				});
			}
			return value;
		}
	},

	cache: {
		_values: {},

		/**
		 * @param {String} key
		 * @param {Function} getter
		 * @param {Object} [context]
		 * @return {*}
		 */
		get: function(key, getter, context) {
			if (!(key in this._values)) {
				this._values[key] = getter.call(context);
			}
			return this._values[key];
		}
	},

	ui: {
		/**
		 * @param {String} question
		 * @param {Function} callback
		 */
		confirm: function(question, callback) {
			var $ok = $('<input type="button" />').val(cm.language.get('Ok'));
			var $cancel = $('<input type="button" />').val(cm.language.get('Cancel'));
			var $html = $('<div><div class="box_cap clearfix nowrap"><h2></h2></div><div class="box_body"></div><div class="box_bottom"></div></div>');
			$html.find('.box_cap h2').text(cm.language.get('Confirmation'));
			$html.find('.box_body').text(question);
			$html.find('.box_bottom').append($ok, $cancel);

			$html.floatOut();
			$ok.click(function() {
				$html.floatIn();
				callback();
			});
			$cancel.click(function() {
				$html.floatIn();
			});
		}
	},

	template: {
		ready: function() {
			_.templateSettings = {
				evaluate: /\[\[(.+?)\]\]/g,
				interpolate: /\[\[=(.+?)\]\]/g,
				escape: /\[\[-(.+?)\]\]/g
			};
		},

		/**
		 * @param {String} template
		 * @param {Object} variables
		 * @return {jQuery}
		 */
		render: function(template, variables) {
			var $output = $(_.template(template, variables).replace(/^\s+|\s+$/g, ''));
			cm.dom.setup($output);
			return $output;
		}
	},

	window: {
		_hasFocus: true,
		_$hidden: null,

		ready: function() {
			var handler = this;
			$(window).focus(function() {
				handler._hasFocus = true;
			}).blur(function() {
					handler._hasFocus = false;
				});
			this.title.ready();
		},

		hasFocus: function() {
			return this._hasFocus;
		},

		appendHidden: function(html) {
			if (!this._$hidden) {
				this._$hidden = $('<div style="display:none;" />').appendTo('body');
			}
			this._$hidden.append(html);
		},

		hint: function(content) {
			$.windowHint(content);
		},

		title: {
			_messageStop: function() {
			},
			_messageTimeout: null,

			ready: function() {
				var handler = this;
				$(window).focus(function() {
					handler._messageStop();
				});
			},

			message: function(msg) {
				if (cm.window.hasFocus()) {
					return;
				}
				var handler = this;
				var sleeper = function(offset) {
					offset += 4;
					if (offset >= msg.length) {
						handler._messageStop();
					} else {
						document.title = msg.substring(offset, msg.length);
						handler._messageTimeout = setTimeout(function() {
							sleeper(offset);
						}, 400);
					}
				};

				this._messageStop();
				var originalTitle = document.title;
				document.title = msg;
				this._messageTimeout = setTimeout(function() {
					sleeper(0);
				}, 1500);
				this._messageStop = function() {
					document.title = originalTitle;
					clearTimeout(handler._messageTimeout);
				};
			}
		}
	},

	storage: {
		/**
		 * @param {String} key
		 * @param {Object} value
		 */
		set: function(key, value) {
			$.jStorage.set(key, value);
		},

		/**
		 * @param {String} key
		 * @return {*}
		 */
		get: function(key) {
			return $.jStorage.get(key);
		},

		/**
		 * @param {String} key
		 */
		del: function(key) {
			$.jStorage.deleteKey(key);
		}
	},

	/**
	 * @param {String} type
	 * @param {Object} data
	 * @param {Object} callbacks
	 */
	ajax: function(type, data, callbacks) {
		var url = this.getUrlAjax(type);
		var errorHandler = function(msg, type, isPublic, callback) {
			if (!callback || callback(msg, type, isPublic) !== false) {
				cm.error.trigger(msg, type, isPublic);
			}
		};
		return $.ajax(url, {
			data: JSON.stringify(data),
			type: 'POST',
			dataType: 'json',
			contentType: 'application/json',
			cache: false,
			success: function(response) {
				if (response.error) {
					errorHandler(response.error.msg, response.error.type, response.error.isPublic, callbacks.error);
				} else if (response.success) {
					if (callbacks.success) {
						callbacks.success(response.success);
					}
				}
			},
			error: function(xhr, textStatus) {
				if (xhr.status == 0) {
					return; // Ignore interrupted ajax-request caused by leaving a page
				}
				var msg = xhr.responseText || textStatus;
				errorHandler(msg, 'XHR', false, callbacks.error);
			},
			complete: function() {
				if (callbacks.complete) {
					callbacks.complete();
				}
			}
		});
	},

	/**
	 * @param {String} methodName
	 * @param {Object} params
	 * @param {Object|Null} callbacks
	 */
	rpc: function(methodName, params, callbacks) {
		callbacks = callbacks || {};
		this.ajax('rpc', {method: methodName, params: params}, {
			success: function(response) {
				if (typeof(response.result) === 'undefined') {
					cm.error.trigger('RPC response has undefined result');
				}
				if (callbacks.success) {
					callbacks.success(response.result);
				}
			},
			error: callbacks.error,
			complete: callbacks.complete
		});
	},

	stream: {
		/** @type {CM_Stream_Adapter_Message_Abstract} */
		_adapter: null,

		/** @type {Object} */
		_channelDispatchers: {},

		/**
		 * @param {String} channel
		 * @param {String} namespace
		 * @param {Function} callback fn(array data)
		 * @param {Object} [context]
		 */
		bind: function(channel, namespace, callback, context) {
			if (!cm.options.stream.enabled) {
				return;
			}
			if (!this._channelDispatchers[channel]) {
				this._subscribe(channel);
			}
			this._channelDispatchers[channel].on(namespace, callback, context);
		},

		/**
		 * @param {String} channel
		 * @param {String} [namespace]
		 * @param {Function} [callback]
		 * @param {Object} [context]
		 */
		unbind: function(channel, namespace, callback, context) {
			if (!this._channelDispatchers[channel]) {
				return;
			}
			this._channelDispatchers[channel].off(namespace, callback, context);
			if (this._getBindCount(channel) === 0) {
				this._unsubscribe(channel);
			}
		},

		/**
		 * @param {String} channel
		 * @return {Integer}
		 */
		_getBindCount: function(channel) {
			if (!this._channelDispatchers[channel] || !this._channelDispatchers[channel]._callbacks) {
				return 0;
			}
			return _.size(this._channelDispatchers[channel]._callbacks);
		},

		/**
		 * @return {CM_Stream_Adapter_Message_Abstract}
		 */
		_getAdapter: function() {
			if (!this._adapter) {
				this._adapter = new window[cm.options.stream.adapter](cm.options.stream.options);
			}
			return this._adapter;
		},

		/**
		 * @param {String} channel
		 */
		_subscribe: function(channel) {
			var handler = this;
			this._channelDispatchers[channel] = _.clone(Backbone.Events);
			this._getAdapter().subscribe(channel, {sessionId: $.cookie('sessionId')}, function(message) {
				if (handler._channelDispatchers[channel]) {
					handler._channelDispatchers[channel].trigger(message.namespace, message.data);
				}
			});
		},

		/**
		 * @param {String} channel
		 */
		_unsubscribe: function(channel) {
			if (this._channelDispatchers[channel]) {
				delete this._channelDispatchers[channel];
			}
			this._adapter.unsubscribe(channel);
		}
	},

	viewEvents: {
		/**
		 * @type {Object}
		 */
		_dispatcher: _.clone(Backbone.Events),

		/**
		 * @param {CM_View_Abstract} view
		 * @param {String} childViewName
		 * @param {String} eventName
		 * @return {String}
		 */
		_getEventName: function(view, childViewName, eventName) {
			return view.getAutoId() + ':' + childViewName + ':' + eventName;
		},

		/**
		 * @param {CM_View_Abstract} view
		 * @param {String} childViewName
		 * @param {String} eventName
		 * @param {Function} callback fn(CM_View_Abstract view, array data)
		 * @param {Object} [context]
		 */
		bind: function(view, childViewName, eventName, callback, context) {
			this._dispatcher.on(this._getEventName(view, childViewName, eventName), callback, context);
		},

		/**
		 * @param {CM_View_Abstract} view
		 * @param {String} childViewName
		 * @param {String} eventName
		 * @param {Function} callback fn(CM_View_Abstract view, array data)
		 * @param {Object} [context]
		 */
		unbind: function(view, childViewName, eventName, callback, context) {
			this._dispatcher.off(this._getEventName(view, childViewName, eventName), callback, context);
		},

		/**
		 * @param {CM_View_Abstract} view
		 * @param {String} eventName
		 * @param {*} data
		 */
		trigger: function(view, eventName, data) {
			var parent = view;
			while (parent = parent.getParent()) {
				this._dispatcher.trigger(this._getEventName(parent, view.getClass(), eventName), view, data);
			}
		}
	},

	model: {
		types: {
		}
	},

	action: {
		verbs: {},

		/**
		 * @param {Number} actionVerb
		 * @param {Number} modelType
		 * @param {Function} callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
		 * @param {String} [streamChannel]
		 * @param {Object} [context]
		 */
		bind: function(actionVerb, modelType, callback, streamChannel, context) {
			streamChannel = streamChannel || cm.options.stream.channel;
			if (!streamChannel) {
				return;
			}
			cm.stream.bind(streamChannel, 'CM_Action_Abstract:' + actionVerb + ':' + modelType, callback, context);
		},
		/**
		 * @param {Number} actionVerb
		 * @param {Number} modelType
		 * @param {Function} [callback]
		 * @param {String} [streamChannel]
		 * @param {Object} [context]
		 */
		unbind: function(actionVerb, modelType, callback, streamChannel, context) {
			streamChannel = streamChannel || cm.options.stream.channel;
			if (!streamChannel) {
				return;
			}
			cm.stream.unbind(streamChannel, 'CM_Action_Abstract:' + actionVerb + ':' + modelType, callback, context);
		}
	},

	keyCode: {
		ALT: 18,
		BACKSPACE: 8,
		CAPS_LOCK: 20,
		COMMA: 188,
		COMMAND: 91,
		COMMAND_LEFT: 91,
		COMMAND_RIGHT: 93,
		CONTROL: 17,
		DELETE: 46,
		DOWN: 40,
		END: 35,
		ENTER: 13,
		ESCAPE: 27,
		HOME: 36,
		INSERT: 45,
		LEFT: 37,
		MENU: 93,
		NUMPAD_ADD: 107,
		NUMPAD_DECIMAL: 110,
		NUMPAD_DIVIDE: 111,
		NUMPAD_ENTER: 108,
		NUMPAD_MULTIPLY: 106,
		NUMPAD_SUBTRACT: 109,
		PAGE_DOWN: 34,
		PAGE_UP: 33,
		PERIOD: 190,
		RIGHT: 39,
		SHIFT: 16,
		SPACE: 32,
		TAB: 9,
		UP: 38,
		WINDOWS: 91
	},

	router: {
		_router: null,
		start: function() {
			var $placeholder;
			var request;
			var urlBase = cm.getUrl();
			var pushState = Modernizr.history;
			var Router = Backbone.Router.extend({
				routes: {
					'*path': 'page'
				},
				page: function(url) {
					url = '/' + url;
					if (!$placeholder) {
						$placeholder = $('<div class="router-placeholder" />');
						var page = cm.findView('CM_Page_Abstract');
						page.$().replaceWith($placeholder);
						page.remove(true);
						cm.router.onTeardown();
					} else {
						$placeholder.removeClass('error').html('');
					}
					var timeoutLoading = window.setTimeout(function() {
						$placeholder.html('<div class="spinner" />')
					}, 750);
					if (request) {
						request.abort();
					}
					request = cm.findView().loadPage(url, {
						success: function(response) {
							var fragment = response.url.substr(urlBase.length);
							var currentLayout = cm.findView('CM_Layout_Abstract');
							var reload = currentLayout && (currentLayout.getClass() != response.layoutClass);
							if (reload) {
								var reloadUrl = response.url;
								if (!pushState) {
									reloadUrl += '#' + fragment.substr(1);
								}
								window.location.replace(reloadUrl);
								return;
							}
							$placeholder.replaceWith(this.$());
							$placeholder = null;
							cm.router._router.navigate(fragment, {trigger: false, replace: true});
							cm.router.onSetup(this, response.title, response.url, response.menuEntryHashList);
						},
						error: function(msg, type, isPublic) {
							$placeholder.addClass('error').html('<pre>' + msg + '</pre>');
							cm.router.onError();
							return false;
						},
						complete: function() {
							window.clearTimeout(timeoutLoading);
						}
					});
				}
			});
			this._router = new Router();
			var hash = window.location.hash.substr(1);
			var path = window.location.pathname.substr(1) + window.location.search;
			var fragment = path;
			var trigger = false;
			if (hash) {
				fragment = hash;
				if (hash != path) {
					trigger = true;
				}
			}
			Backbone.history.start({pushState: pushState, silent: true});
			Backbone.history.fragment = path;
			if (!pushState && !hash && path) {
				Backbone.history.fragment = '';	// Force navigation to add hash to URL field
			}
			this._router.navigate(fragment, {trigger: trigger, replace: true});
			$(document).on('clickNoMeta', 'a[href]:not([data-router-disabled=true])', function(event) {
				if (0 === this.href.indexOf(urlBase)) {
					var fragment = this.href.substr(urlBase.length);
					var forceReload = $(this).data('force-reload');
					cm.router.route(fragment, forceReload);
					event.preventDefault();
				}
			});
		},

		/**
		 * @param {String} url
		 * @param {Boolean} [forceReload]
		 */
		route: function(url, forceReload) {
			forceReload = forceReload || false;
			var urlBase = cm.getUrl();
			var fragment = url;
			if ('/' == url.charAt(0)) {
				url = urlBase + fragment;
			} else {
				fragment = url.substr(urlBase.length);
			}
			if (!this._router || forceReload || 0 !== url.indexOf(urlBase)) {
				window.location.assign(url);
				return;
			}
			this._router.navigate(fragment, {trigger: true});
			cm.findView('CM_Layout_Abstract').trigger('route', url);
		},

		/**
		 * @param {CM_Page_Abstract} page
		 * @param {String} title
		 * @param {String} url
		 * @param {String[]} menuEntryHashList
		 */
		onSetup: function(page, title, url, menuEntryHashList) {
			document.title = title;
			$('[data-menu-entry-hash]').removeClass('active');
			var menuEntrySelectors = _.map(menuEntryHashList, function(menuEntryHash) {
				return '[data-menu-entry-hash=' + menuEntryHash + ']';
			});
			$(menuEntrySelectors.join(',')).addClass('active');
		},

		onTeardown: function() {
			$(document).scrollTop(0);
			$('.floatbox-layer').floatIn();
		},

		onError: function() {
			$('[data-menu-entry-hash]').removeClass('active');
		}
	}
});
