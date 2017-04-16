/**
 * @class CM_View_Abstract
 * @extends Backbone.View
 */
var CM_View_Abstract = Backbone.View.extend({
  _class: 'CM_View_Abstract',

  /** @type CM_View_Abstract[] **/
  _children: [],

  /** @type Function */
  _mockMethod: function() {},

  /**
   * @param {Object} [options]
   */
  constructor: function(options) {
    this.options = options || {};
    Backbone.View.apply(this, arguments);
  },

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
    if (this.appEvents) {
      this._bindAppEvents(this.appEvents);
    }
    this.on('all', function(eventName, data) {
      cm.viewEvents.trigger.apply(cm.viewEvents, [this].concat(_.toArray(arguments)));
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
    this.trigger('ready');
  },

  /**
   * @param {CM_View_Abstract} child
   */
  registerChild: function(child) {
    this._children.push(child);
    child.options.parent = this;
  },

  /**
   * @param {String|Null} [className]
   * @return CM_View_Abstract[]
   */
  getChildren: function(className) {
    if (!className) {
      return this._children;
    }
    return _.filter(this._children, function(child) {
      return child.hasClass(className);
    });
  },

  /**
   * @param {String} className
   * @return CM_View_Abstract|null
   */
  findChild: function(className) {

    var child = _.find(this.getChildren(), function(child) {
      return child.hasClass(className);
    });

    var start = window.performance ? performance.now() : console.time('childMocking');
    var mock;
    for (var i = 0; i < 1000; i++) {
      mock = this._mockChild(window[className].prototype);
    }

    var end = window.performance ? performance.now() : console.timeEnd('childMocking');
    if (end && start) {
      console.log('childMocking time: ' + (end - start));
    }
    return child;
  },

  /**
   * @param {Object} proto
   * @returns {Object}
   */
  _mockChild: function(proto){
    var mock = {};
    for (var key in proto) {
      if (proto[key] instanceof Function) {
        mock[key] = this._mockMethod;
      }
    }
    //console.log('### ' + Object.keys(mock).length, mock);
    return mock;
  },

  /**
   * @param {String} className
   * @returns {CM_View_Abstract}
   */
  getChild: function(className) {
    var child = this.findChild(className);
    if (!child) {
      throw new Error('Failed to retrieve `' + className + '` child view of `' + this.getClass() + '`.');
    }
    return child;
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
   * @return CM_View_Abstract|null
   */
  getComponent: function() {
    if (this.hasClass('CM_Component_Abstract')) {
      return this;
    }
    return this.findParent('CM_Component_Abstract');
  },

  /**
   * @returns {{CM_Component_Abstract: Object|null, CM_View_Abstract: Object}}
   */
  getViewInfoList: function() {
    var viewInfoList = {
      CM_Component_Abstract: null,
      CM_View_Abstract: this._getArray()
    };
    var component = this.getComponent();
    if (component) {
      viewInfoList.CM_Component_Abstract = component._getArray();
    }
    return viewInfoList;
  },

  /**
   * @return String
   */
  getAutoId: function() {
    if (!this.el.id) {
      this.el.id = cm.getUuid();
    }
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

  remove: function() {
    this.trigger('destruct');
    this.$el.detach();  // Detach from DOM to prevent reflows when removing children

    _.each(_.clone(this.getChildren()), function(child) {
      child.remove();
    });

    if (this.getParent()) {
      var siblings = this.getParent().getChildren();
      for (var i = 0, sibling; sibling = siblings[i]; i++) {
        if (sibling.getAutoId() == this.getAutoId()) {
          siblings.splice(i, 1);
        }
      }
    }

    delete cm.views[this.getAutoId()];
    this.$el.remove();
    this.stopListening();
  },

  /**
   * @param {jQuery} $html
   */
  replaceWithHtml: function($html) {
    var parent = this.el.parentNode;
    parent.replaceChild($html[0], this.el);
    this.remove();
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
   * @return Promise
   */
  ajax: function(functionName, params, options) {
    options = _.defaults(options || {}, {
      'modal': false,
      'view': this
    });
    params = params || {};
    var handler = this;

    if (options.modal) {
      this.disable();
    }

    var promise = this
      .try(function() {
        return cm.ajax('ajax', {
          viewInfoList: options.view.getViewInfoList(),
          method: functionName,
          params: params
        });
      })
      .then(function(response) {
        if (response.exec) {
          new Function(response.exec).call(handler);
        }
        return response.data;
      })
      .finally(function() {
        if (options.modal) {
          handler.enable();
        }
      });

    this.on('destruct', function() {
      promise.cancel();
    });
    return promise;
  },

  /**
   * @param {String} functionName
   * @param {Object} [params]
   * @param {Object} [options]
   * @return Promise
   */
  ajaxModal: function(functionName, params, options) {
    options = _.defaults(options || {}, {
      'modal': true
    });
    return this.ajax(functionName, params, options);
  },

  /**
   * @param {String} className
   * @param {Object} [params]
   * @param {Object} [options]
   * @return Promise
   */
  loadComponent: function(className, params, options) {
    options = _.defaults(options || {}, {
      'modal': true,
      'method': 'loadComponent'
    });
    params = params || {};
    params.className = className;
    return this
      .try(function() {
        return this.ajax(options.method, params, options);
      })
      .then(function(response) {
        return this._injectView(response);
      });
  },

  /**
   * @param {String} className
   * @param {Object|Null} [params]
   * @param {Object|Null} [options]
   * @return Promise
   */
  prepareComponent: function(className, params, options) {
    return this.loadComponent(className, params, options)
      .then(function(component) {
        component._ready();
        return component;
      });
  },

  /**
   * @param {String} className
   * @param {Object|Null} [params]
   * @param {Object|Null} [options]
   * @return Promise
   */
  popOutComponent: function(className, params, options) {
    return this.prepareComponent(className, params, options)
      .then(function(component) {
        component.popOut({}, true);
        return component;
      });
  },

  /**
   * @param {int} actionVerb
   * @param {int} actionType
   * @param {String} [channelKey]
   * @param {Number} [channelType]
   * @param {Function} callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
   */
  bindAction: function(actionVerb, actionType, channelKey, channelType, callback) {
    if (!channelKey && !channelType) {
      if (!cm.options.stream.channel) {
        return;
      }
      channelKey = cm.options.stream.channel.key;
      channelType = cm.options.stream.channel.type;
    }
    var callbackResponse = function(response) {
      callback.call(this, response.action, response.model, response.data);
    };
    cm.action.bind(actionVerb, actionType, channelKey, channelType, callbackResponse, this);
    this.on('destruct', function() {
      cm.action.unbind(actionVerb, actionType, channelKey, channelType, callbackResponse, this);
    });
  },

  /**
   * @param {String} channelKey
   * @param {Number} channelType
   * @param {String} event
   * @param {Function} callback fn(array data)
   * @param {Boolean} [allowClientMessage]
   */
  bindStream: function(channelKey, channelType, event, callback, allowClientMessage) {
    cm.stream.bind(channelKey, channelType, event, callback, this, allowClientMessage);
    this.on('destruct', function() {
      cm.stream.unbind(channelKey, channelType, event, callback, this);
    }, this);
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
   * @param {String} event
   * @param {Function} callback fn(Object data)
   */
  bindAppEvent: function(event, callback) {
    cm.event.bind(event, callback, this);
    this.on('destruct', function() {
      cm.event.unbind(event, callback, this);
    });
  },

  /**
   * @param {Function} callback
   * @param {Number} interval
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
   * @param {Number} timeout
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
   * @param {jQuery} $element
   * @param {String} event
   * @param {Function} callback
   */
  bindJquery: function($element, event, callback) {
    var self = this;
    var callbackWithContext = function() {
      callback.apply(self, arguments);
    };
    $element.on(event, callbackWithContext);
    this.on('destruct', function() {
      $element.off(event, callbackWithContext);
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
        throw new CM_Exception('Template `' + name + '` does not exist in `' + this.getClass() + '`');
      }
      return $template.html();
    });
    return cm.template.render(template, variables);
  },


  /**
   * @param {Function} callback
   * @returns {Promise}
   */
  try: function(callback) {
    return Promise.try(callback.bind(this)).bind(this);
  },

  /**
   * @param {Number} milliseconds
   * @returns {Promise}
   */
  delay: function(milliseconds) {
    return Promise.delay(milliseconds).bind(this);
  },

  /**
   * @param {String} eventName
   * @param {*} [obj]
   * @returns {Promise}
   */
  wait: function(eventName, obj) {
    var observer = new cm.lib.Observer();
    var target = obj || this;
    var promise = new Promise(function(resolve) {
      observer.listenTo(target, eventName, resolve);
    });
    return promise.finally(function() {
      observer.stopListening();
      observer = null;
    });
  },

  /**
   * @param {Function} callback
   * @param {String} eventName
   * @param {*} [obj]
   */
  cancellable: function(callback, eventName, obj) {
    var target = obj || this;
    var observer = new cm.lib.Observer();
    var promise = null;
    observer.listenTo(target, eventName, function() {
      if (promise) {
        promise.cancel();
      }
    });
    promise = callback.apply(this);

    return promise.finally(function() {
      observer.stopListening();
      observer = null;
    });
  },

  /**
   * @param {Object} actions
   * @param {String} [channelKey]
   * @param {Number} [channelType]
   */
  _bindActions: function(actions, channelKey, channelType) {
    _.each(actions, function(callback, key) {
      var match = key.match(/^(\S+)\s+(.+)$/);
      var actionType = cm.action.types[match[1]];
      var actionNames = match[2].split(/\s*,\s*/);
      _.each(actionNames, function(actionVerbName) {
        this.bindAction(actionVerbName, actionType, channelKey, channelType, callback);
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
   * @param {Object} events
   */
  _bindAppEvents: function(events) {
    _.each(events, function(callback, eventNameStr) {
      var eventNameList = eventNameStr.split(/[\s]+/);
      _.each(eventNameList, function(eventName) {
        this.bindAppEvent(eventName, callback);
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
   * @return CM_Abstract_View
   */
  _injectView: function(response) {
    cm.window.appendHidden(response.html);
    new Function(response.js).call(this);
    var view = cm.views[response.autoId];
    this.registerChild(view);
    return view;
  },

  /**
   * @param {Object} response
   * @return CM_Abstract_View
   */
  _replaceView: function(response) {
    cm.window.appendHidden(response.html);
    new Function(response.js).call(this);
    var view = cm.views[response.autoId];
    this.getParent().registerChild(view);
    this.replaceWithHtml(view.$el);
    view._ready();
    return view;
  }
});
