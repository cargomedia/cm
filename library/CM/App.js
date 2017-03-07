/**
 * @class CM_App
 * @extends CM_Class_Abstract
 */
var CM_App = CM_Class_Abstract.extend({
  /** @type {Object} **/
  views: {},

  /** @type {Logger} **/
  logger: (cm.logger || null),

  /** @type {Object} **/
  lib: (cm.lib || {}),

  /** @type {Object|Null} **/
  viewer: null,

  /** @type {Object} **/
  options: {},

  ready: function() {
    this.logger.configure({
      dev: cm.options.debug
    });
    this.promise.ready();
    this.error.ready();
    this.dom.ready();
    this.window.ready();
    this.date.ready();
    this.template.ready();
    this.router.ready();
    this.stream.ready();
  },

  /**
   * @returns {Number}
   */
  getDeployVersion: function() {
    return cm.options.deployVersion;
  },

  /**
   * @return {Number}
   */
  getSiteId: function() {
    return cm.options.site.type;
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
        throw new CM_Exception('Cannot find root component');
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
  getViewList: function(className) {
    if (!className) {
      return this.views;
    }
    return _.filter(this.views, function(view) {
      return view.hasClass(className);
    });
  },

  /**
   * @return {CM_Layout_Abstract}
   */
  getLayout: function() {
    var layout = this.findView('CM_Layout_Abstract');
    if (!layout) {
      throw new CM_Exception('Cannot find layout');
    }
    return layout;
  },

  /**
   * @returns {CM_View_Document}
   */
  getDocument: function() {
    var document = this.findView('CM_View_Document');
    if (!document) {
      throw new CM_Exception('Cannot find document');
    }
    return document;
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
   * @param {Object} [params]
   * @param {Boolean} [relative]
   * @return {String}
   */
  getUrl: function(path, params, relative) {
    path = path || '';
    params = params || null;
    relative = relative || false;
    if (params) {
      path += '?' + jQuery.param(params);
    }
    if (!relative) {
      path = cm.options.url + path
    }
    return path;
  },

  /**
   * @param {String} path
   * @return {String}
   */
  getUrlStatic: function(path) {
    var url = '';
    if (cm.options.urlCdn) {
      url = cm.options.urlCdn + url;
    } else {
      url = cm.options.urlBase + url;
    }

    url += '/static';
    if (path) {
      url += path + '?' + cm.options.deployVersion;
    }

    return url;
  },

  /**
   * @param {String} type
   * @param {String} path
   * @param {Object} [options]
   * @return {String}
   */
  getUrlResource: function(type, path, options) {
    options = _.defaults(options || {}, {
      'sameOrigin': false
    });

    var url = '';
    if (!options['sameOrigin'] && cm.options.urlCdn) {
      url = cm.options.urlCdn + url;
    } else {
      url = cm.options.urlBase + url;
    }

    if (type && path) {
      var urlParts = [];
      urlParts.push(type);
      if (cm.options.language) {
        urlParts.push(cm.options.language.abbreviation);
      }
      urlParts.push(cm.getSiteId());
      urlParts.push(cm.options.deployVersion);
      urlParts = urlParts.concat(path.split('/'));

      url += '/' + urlParts.join('/');
    }

    return url;
  },

  /**
   * @returns {string}
   */
  getUrlServiceWorker: function() {
    return cm.options.urlServiceWorker;
  },

  /**
   * @param {String} path
   * @return {String}
   */
  getUrlUserContent: function(path) {
    var matches = path.match(new RegExp('^([^/]+)/'));
    if (null === matches) {
      throw new CM_Exception('Cannot detect namespace for user-content file `' + path + '`.');
    }
    var namespace = matches[1];
    var urlList = cm.options.urlUserContentList;
    var url = _.has(urlList, namespace) ? urlList[namespace] : urlList['default'];
    return url + '/' + path;
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
    return this.getUrl(path);
  },

  /**
   * @returns {String}
   */
  getClientId: function() {
    return $.cookie('clientId');
  },

  /**
   * @returns {Object}
   */
  getContext: function() {
    var context = {};
    context[cm.options.name] = {
      client: cm.getClientId(),
      browserWindow: cm.window.getId()
    };
    if (cm.viewer) {
      context[cm.options.name].user = cm.viewer.id;
    }
    return context;
  },

  factory: {

    /**
     * @param {*} data
     * @returns {*|CM_Model_Abstract}
     */
    create: function(data) {
      return this._create(data);
    },

    /**
     * @param {*} data
     * @returns {*|CM_Model_Abstract}
     */
    _create: function(data) {
      if ($.isPlainObject(data) || _.isArray(data)) {
        _.each(data, function(value, key) {
          data[key] = this._create(value);
        }.bind(this));
      }
      if (this._isCmObject(data)) {
        return this._toCmObject(data);
      }
      return data;
    },

    /**
     * @param {*} data
     * @returns {Boolean}
     */
    _isCmObject: function(data) {
      var className = data && data['_class'];
      var isClass = className && window[className];
      var isBackbone = data instanceof Backbone.Model || data instanceof Backbone.Collection;
      return isClass && !isBackbone;
    },

    /**
     * @param {Object} data
     * @returns {CM_Model_Abstract}
     */
    _toCmObject: function(data) {
      return new window[data['_class']](data);
    }
  },

  promise: {
    ready: function() {
      var promiseConfig = {};
      if (cm.options.debug) {
        promiseConfig['warnings'] = {
          wForgottenReturn: false
        };
      } else {
        promiseConfig['warnings'] = false;
      }
      Promise.config(promiseConfig);
    }
  },

  error: {
    _handlers: [],

    ready: function() {
      $(window).on('unhandledrejection', function(e) {
        e.preventDefault();
        var event = e.originalEvent;
        var error = null;
        if (event.detail && event.detail.reason) {
          error = event.detail.reason;
        } else if (event.reason) {
          error = event.reason;
        } else {
          error = new Error('Unhandled promise rejection without reason.');
        }
        if (!(error instanceof Promise.CancellationError)) {
          cm.error.handle(error);
        }
      });
    },

    /**
     * @param {{String:Function}} handlers
     * @param {*} [context]
     */
    registerHandlers: function(handlers, context) {
      _.each(handlers, function(callback, errorName) {
        cm.error.registerHandler(errorName, callback, context);
      });
    },

    /**
     * @param {String} errorName
     * @param {Function} callback
     * @param {*} [context]
     */
    registerHandler: function(errorName, callback, context) {
      context = context || window;
      cm.error._handlers.push({
        errorName: errorName,
        callback: callback,
        context: context
      });
    },

    /**
     * @param {String} errorName
     * @param {Function} [callback]
     * @param {*} [context]
     */
    unregisterHandler: function(errorName, callback, context) {
      cm.error._handlers = _.reject(cm.error._handlers, function(handler) {
        return (
          errorName === handler.errorName &&
          (_.isFunction(callback) ? callback === handler.callback : true) &&
          (!_.isUndefined(context) ? context === handler.context : true)
        );
      });
    },

    /**
     * @param {Error} error
     */
    log: function(error) {
      _.defer(function() {
        cm.logger.addRecordError(error);
        throw error;
      });
    },

    /**
     * @param {Error} error
     * @throws Error
     */
    handle: function(error) {
      var throwError = true;
      if (error instanceof CM_Exception) {
        var handlers = _.filter(cm.error._handlers, function(handler) {
          return handler.errorName === error.name;
        });
        if (0 !== handlers.length) {
          throwError = false;
          _.every(handlers, function(handler) {
            return false !== handler.callback.call(handler.context, error);
          });
        } else {
          cm.window.hint(error.message);
        }
      }
      if (throwError) {
        throw error;
      }
    }
  },

  debug: {

    /**
     * @param {CM_View_Abstract|String} [view]
     */
    viewTree: function(view) {
      if (!(view instanceof CM_View_Abstract)) {
        view = cm.findView(view ? String(view) : view);
      }
      if (!view) {
        throw new Error('View not found');
      }
      console.group(view.getClass(), view.el);
      _.each(view.getChildren(), function(child) {
        cm.debug.viewTree(child);
      });
      console.groupEnd();
    },

    /**
     * @param {*...} messages
     */
    log: function(messages) {
      var args = _.toArray(arguments);
      var message = args.shift();
      var time = (Date.now() - performance.timing.navigationStart) / 1000;
      var timeFormatted = time.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2, useGrouping: false});
      var prefix = '[CM ' + timeFormatted + (performance.timing.isPolyfilled ? '!' : '') + ']';
      cm.logger.debug.apply(cm.logger, [prefix + ' ' + message].concat(args));
    }
  },

  dom: {
    _swfId: 0,
    ready: function() {
      window.viewportUnitsBuggyfill.init();
    },
    /**
     * @param {jQuery} $dom
     */
    setup: function($dom) {
      $dom.find('.timeago').timeago();
      $dom.find('.clipSlide').clipSlide();
      $dom.find('.toggleNext').toggleNext();
      $dom.find('.tabs').tabs();
      $dom.find('.revive-ad:visible').revive();
      $dom.find('.fancySelect').fancySelect();
      this._setupContentPlaceholder($dom);
    },

    /**
     * @param {jQuery} $dom
     */
    _setupContentPlaceholder: function($dom) {
      var $doNotLoadOnReady = $();
      $dom.find('.clipSlide').each(function() {
        var $notFirstImages = $(this).find('.contentPlaceholder:gt(0)');
        $doNotLoadOnReady = $doNotLoadOnReady.add($notFirstImages);

        $(this).on('toggle.clipSlide', function() {
          $(this).find('.contentPlaceholder:gt(0)').lazyImageSetup();
        });
      });

      $dom.find('.contentPlaceholder').not($doNotLoadOnReady).lazyImageSetup();
    },

    /**
     * @param {jQuery} $dom
     */
    teardown: function($dom) {
      $dom.find('.openerDropdown').opener('close');
      $dom.find('.timeago').timeago('dispose');
      $dom.find('img.lazy').trigger('destroy.unveil');
    },

    /**
     * @param {String|String[]} sourceList
     * @param {Object} [options]
     * @param {Boolean} [options.loop=false]
     * @param {Boolean} [options.autoplay=false]
     * @return {Audio}
     */
    createAudio: function(sourceList, options) {
      sourceList = _.isString(sourceList) ? [sourceList] : sourceList;
      var audio = new cm.lib.Media.Audio();
      audio.setOptions(options);
      audio.setSources(sourceList);
      return audio;
    }
  },

  string: {
    padLeft: function(str, length, character) {
      character = character || ' ';
      var string = String(str);
      return new Array(length - string.length + 1).join(character) + string;
    }
  },

  date: {
    ready: function() {
      $.timeago.settings.allowFuture = true;
      $.timeago.settings.autoDispose = false;
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
      $.cookie('timezoneOffset', (new Date()).getTimezoneOffset() * 60);
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
     * @param {Number} [timestamp]
     * @param {String} [cssClass]
     * @return {jQuery}
     */
    timeago: function(timestamp, cssClass) {
      var date;
      if (timestamp) {
        date = new Date(timestamp * 1000);
      } else {
        date = new Date();
      }
      var print = date.toLocaleString();
      var iso8601 = this.iso8601(date);
      cssClass += ' timeago';
      return '<time datetime="' + iso8601 + '" class="' + cssClass + '">' + print + '</time>';
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
     * @param {Object} [context]
     */
    confirm: function(question, callback, context) {
      var $ok = $('<input type="button" class="button button-default" />').val(cm.language.get('Ok'));
      var $cancel = $('<input type="button" class="button button-default" />').val(cm.language.get('Cancel'));
      var $html = $('<div class="box"><div class="box-header nowrap"><h2></h2></div><div class="box-body"></div><div class="box-footer"></div></div>');
      $html.find('.box-header h2').text(cm.language.get('Confirmation'));
      $html.find('.box-body').text(question);
      $html.find('.box-footer').append($ok, $cancel);

      $html.floatbox();
      $ok.click(function() {
        $html.floatbox('close');
        callback.call(context);
      });
      $cancel.click(function() {
        $html.floatbox('close');
      });
      $ok.focus();
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
      var $output = $(this.renderHtml(template, variables));
      cm.dom.setup($output);
      return $output;
    },

    /**
     * @param {String} template
     * @param {Object} variables
     * @return {String}
     */
    renderHtml: function(template, variables) {
      var compiled = _.template(template);
      return compiled(variables).replace(/^\s+|\s+$/g, '');
    }
  },

  window: {
    /** @var {String|Null} */
    _id: null,

    /** @var {Boolean} */
    _hasFocus: true,

    /** @var {jQuery|Null} */
    _$hidden: null,

    /**
     * @returns {String}
     */
    getId: function() {
      if (!this._id) {
        this._id = cm.getUuid();
      }
      return this._id;
    },

    focus: {
      /**
       * @return {Array}
       */
      _get: function() {
        var windows = cm.storage.get('focusWindows');
        if (windows === null) {
          windows = [];
        }
        return windows;
      },
      /**
       * @param {Array} focusWindows
       */
      _set: function(focusWindows) {
        cm.storage.set('focusWindows', focusWindows);
      },
      /**
       * @param {String} uuid
       */
      add: function(uuid) {
        if (this.isLast(uuid)) {
          return;
        }
        this.remove(uuid);
        var windows = this._get();
        windows.push(uuid);
        this._set(windows);
      },
      /**
       * @param {String} uuid
       */
      remove: function(uuid) {
        var windows = this._get();
        var index = windows.indexOf(uuid);
        if (index !== -1) {
          windows.splice(index, 1);
          this._set(windows);
        }
      },
      /**
       * @param {String} uuid
       * @returns {Boolean}
       */
      isLast: function(uuid) {
        var windows = this._get();
        var index = windows.indexOf(uuid);
        return index !== -1 && index === windows.length - 1;
      }
    },

    ready: function() {
      var handler = this;
      handler.focus.add(handler.getId());
      $(window).on('beforeunload', function() {
        handler.focus.remove(handler.getId());
      });
      $(window).focus(function() {
        handler.focus.add(handler.getId());
        handler._hasFocus = true;
      }).blur(function() {
        handler._hasFocus = false;
      });
      this.title.ready();
    },

    /**
     * @return {Boolean}
     */
    isLastFocus: function() {
      return this.focus.isLast(this.getId());
    },

    /**
     * @return {Boolean}
     */
    hasFocus: function() {
      return this._hasFocus;
    },

    /**
     * @param {String|jQuery} html
     */
    appendHidden: function(html) {
      if (!this._$hidden) {
        this._$hidden = $('<div style="display:none;" />').appendTo('body');
      }
      this._$hidden.append(html);
    },

    /**
     * @param {Element} element
     * @return Boolean
     */
    isHidden: function(element) {
      if (!this._$hidden) {
        return false;
      }
      return $.contains(this._$hidden[0], element);
    },

    /**
     * @param {String} content
     */
    hint: function(content) {
      $.windowHint(content);
    },

    fastScroll: {
      /** @var {FastScroll|Null} */
      _instance: null,

      enable: function() {
        if (!this._instance) {
          this._instance = new FastScroll();
        }
      },

      disable: function() {
        if (this._instance) {
          this._instance.destroy();
          this._instance = null;
        }
      }
    },

    title: {
      /** @var {String|null} */
      _prefix: null,
      /** @var {String} */
      _text: '',

      ready: function() {
        this.setText(document.title);
      },

      /**
       * @param {String|null} prefix
       */
      setPrefix: function(prefix) {
        this._prefix = prefix;
        this._update();
      },

      /**
       * @param {String} text
       */
      setText: function(text) {
        this._text = text;
        this._update();
      },

      _update: function() {
        var title = '';
        if (this._prefix) {
          title += this._prefix + ' ';
        }
        if (this._text) {
          title += this._text;
        }
        document.title = title;
      }
    }
  },

  storage: {
    /**
     * @param {String} key
     * @param {*} value
     */
    set: function(key, value) {
      this._getPersistentStorage().set(key, value);
    },

    /**
     * @param {String} key
     * @return {*|null}
     */
    get: function(key) {
      var value = this._getPersistentStorage().get(key);
      return !_.isUndefined(value) ? value : null;
    },

    /**
     * @param {String} key
     */
    del: function(key) {
      this._getPersistentStorage().del(key);
    },

    /** @type {PersistentStorage|null} **/
    _data: null,

    /**
     * @returns {PersistentStorage}
     * @private
     */
    _getPersistentStorage: function() {
      if (!this._data) {
        this._data = new cm.lib.PersistentStorage(cm.options.name + ':' + cm.getSiteId(), localStorage, cm.logger);
      }
      return this._data;
    }
  },

  /**
   * @param {String} type
   * @param {Object} data
   * @return Promise
   */
  ajax: function(type, data) {
    var url = this.getUrlAjax(type);
    var jqXHR;

    var ajaxPromise = new Promise(function(resolve, reject, onCancel) {
      jqXHR = $.ajax(url, {
        data: JSON.stringify(data),
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        cache: false
      });
      jqXHR.retry({times: 3, statusCodes: [405, 500, 503, 504]})
        .done(function(response) {
          if (cm.getDeployVersion() != response.deployVersion) {
            cm.router.forceReload();
          }
          if (response.error) {
            reject(new (CM_Exception.factory(response.error.type))(response.error.msg, response.error.isPublic, response.error.metaInfo));
          } else {
            resolve(cm.factory.create(response.success));
          }
        })
        .fail(function(xhr, textStatus) {
          if (xhr.status === 0) {
            if (window.navigator.onLine) {
              ajaxPromise.cancel();
            } else {
              reject(new CM_Exception_RequestFailed(cm.language.get('No Internet connection')));
            }
          } else {
            var msg = cm.language.get('An unexpected connection problem occurred.');
            if (cm.options.debug) {
              msg = xhr.responseText || textStatus;
            }
            reject(new CM_Exception(msg));
          }
        });
      onCancel(function() {
        if (jqXHR) {
          jqXHR.abort();
        }
      });
    });
    return ajaxPromise;
  },

  /**
   * @param {String} methodName
   * @param {Object} params
   * @return Promise
   */
  rpc: function(methodName, params) {
    return this.ajax('rpc', {method: methodName, params: params})
      .then(function(response) {
        if (typeof(response.result) === 'undefined') {
          throw new CM_Exception('RPC response has undefined result');
        }
        return response.result;
      });
  },

  stream: {
    /** @type {CM_MessageStream_Adapter_Abstract} */
    _adapter: null,

    /** @type {Object} */
    _channelDispatchers: {},

    ready: function() {
      if (!cm.options.stream.enabled) {
        return;
      }
      if (!cm.options.stream.channel) {
        return;
      }
      if (cm.options.stream.channel.key && cm.options.stream.channel.type) {
        var channel = cm.options.stream.channel.key + ':' + cm.options.stream.channel.type;
        this._subscribe(channel);
      }
    },

    /**
     * @param {String} channelKey
     * @param {Number} channelType
     * @param {String} namespace
     * @param {Function} callback fn(array data)
     * @param {Object} [context]
     * @param {Boolean} [allowClientMessage]
     */
    bind: function(channelKey, channelType, namespace, callback, context, allowClientMessage) {
      var channel = channelKey + ':' + channelType;
      if (!cm.options.stream.enabled) {
        return;
      }
      if (!channelKey || !channelType) {
        throw new CM_Exception('No channel provided');
      }
      if (!this._channelDispatchers[channel]) {
        this._subscribe(channel);
      }
      this._channelDispatchers[channel].on(this._getEventNames(namespace, allowClientMessage), callback, context);
    },

    /**
     * @param {String} channelKey
     * @param {Number} channelType
     * @param {String} [namespace]
     * @param {Function} [callback]
     * @param {Object} [context]
     */
    unbind: function(channelKey, channelType, namespace, callback, context) {
      var channel = channelKey + ':' + channelType;
      if (!this._channelDispatchers[channel]) {
        return;
      }
      if (!channelKey || !channelType) {
        throw new CM_Exception('No channel provided');
      }
      this._channelDispatchers[channel].off(this._getEventNames(namespace, true), callback, context);
      if (this._getBindCount(channel) === 0) {
        this._unsubscribe(channel);
      }
    },

    /**
     * @param {String} channelKey
     * @param {Number} channelType
     * @param {String} event
     * @param {Object} data
     */
    publish: function(channelKey, channelType, event, data) {
      var channel = channelKey + ':' + channelType;
      this._getAdapter().publish(channel, event, data);
    },

    /**
     * @param {String} [namespace]
     * @param {Boolean} [allowClientMessage]
     */
    _getEventNames: function(namespace, allowClientMessage) {
      var eventName = namespace;
      if (namespace && allowClientMessage) {
        eventName += ' client-' + namespace;
      }
      return eventName;
    },

    /**
     * @param {String} channel
     * @return {Number}
     */
    _getBindCount: function(channel) {
      if (!this._channelDispatchers[channel] || !this._channelDispatchers[channel]._events) {
        return 0;
      }
      return _.size(this._channelDispatchers[channel]._events);
    },

    /**
     * @return {CM_MessageStream_Adapter_Abstract}
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
      this._getAdapter().subscribe(channel, {sessionId: $.cookie('sessionId')}, function(event, data) {
        if (handler._channelDispatchers[channel]) {
          data = cm.factory.create(data);
          cm.debug.log('Stream channel (`%s): event `%s`: %o', channel, event, data);
          handler._channelDispatchers[channel].trigger(event, data);
        }
      });
      cm.debug.log('Stream channel (`%s`): subscribe', channel);
    },

    /**
     * @param {String} channel
     */
    _unsubscribe: function(channel) {
      if (this._channelDispatchers[channel]) {
        delete this._channelDispatchers[channel];
      }
      this._adapter.unsubscribe(channel);
      cm.debug.log('Stream channel (`%s`): unsubscribe', channel);
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
     * @param {...*} data
     */
    trigger: function(view, eventName, data) {
      var parent = view;
      var eventArguments = _.toArray(arguments).slice(2);
      while (parent = parent.getParent()) {
        this._dispatcher.trigger.apply(this._dispatcher, [this._getEventName(parent, view.getClass(), eventName), view].concat(eventArguments));
      }
    }
  },

  event: {
    /**
     * @type {Backbone.Events}
     */
    _dispatcher: _.clone(Backbone.Events),

    /**
     * @param {String} eventName
     * @param {Function} callback fn(Object data)
     * @param {Object} [context]
     */
    bind: function(eventName, callback, context) {
      this._dispatcher.on(eventName, callback, context);
    },

    /**
     * @param {String} eventName
     * @param {Function} callback fn(Object data)
     * @param {Object} [context]
     */
    unbind: function(eventName, callback, context) {
      this._dispatcher.off(eventName, callback, context);
    },

    /**
     * @param {String} eventName
     * @param {Object} [data]
     */
    trigger: function(eventName, data) {
      this._dispatcher.trigger(eventName, data);
    }
  },

  model: {
    types: {}
  },

  action: {
    verbs: {},
    types: {},

    /**
     * @param {Number} actionVerb
     * @param {Number} actionType
     * @param {String} channelKey
     * @param {Number} channelType
     * @param {Function} callback fn(CM_Action_Abstract action, CM_Model_Abstract model, array data)
     * @param {Object} [context]
     */
    bind: function(actionVerb, actionType, channelKey, channelType, callback, context) {
      cm.stream.bind(channelKey, channelType, 'CM_Action_Abstract:' + actionVerb + ':' + actionType, callback, context);
    },
    /**
     * @param {Number} actionVerb
     * @param {Number} actionType
     * @param {String} channelKey
     * @param {Number} channelType
     * @param {Function} [callback]
     * @param {Object} [context]
     */
    unbind: function(actionVerb, actionType, channelKey, channelType, callback, context) {
      cm.stream.unbind(channelKey, channelType, 'CM_Action_Abstract:' + actionVerb + ':' + actionType, callback, context);
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

    /** @type {Boolean} **/
    _shouldReload: false,

    /** @type {String|Null} */
    hrefInitialIgnore: null,

    ready: function() {
      var router = this;
      this.hrefInitialIgnore = location.href;

      $(window).on('popstate', function() {
        // this `if` fixes double fire of `popstate` event on the initial page.
        if (router.hrefInitialIgnore === location.href) {
          router.hrefInitialIgnore = null;
          return;
        }
        router.hrefInitialIgnore = null;
        router._handleLocationChange(location.href);
      });

      var urlSite = cm.getUrl();
      $(document).on('click', 'a[href]:not([data-router-disabled=true])', function(event) {
        var metaPressed = (event.ctrlKey || event.metaKey);
        var partOfUrlSite = 0 === this.href.indexOf(urlSite);
        if (!metaPressed && partOfUrlSite) {
          var fragment = this.href.substr(urlSite.length);
          var forceReload = $(this).data('force-reload');
          router.route(fragment, forceReload);
          event.preventDefault();
        }
      });
    },

    forceReload: function() {
      this._shouldReload = true;
    },

    /**
     * @param {String} url
     * @param {Boolean|Null} [forceReload]
     * @param {Boolean|Null} [replaceState]
     * @returns {Promise}
     */
    route: function(url, forceReload, replaceState) {
      forceReload = this._shouldReload || forceReload || false;
      replaceState = replaceState || false;
      var urlSite = cm.getUrl();
      if ('/' == url.charAt(0)) {
        url = urlSite + url;
      }
      var fragment = this._getFragmentByUrl(url);
      if (forceReload || 0 !== url.indexOf(urlSite)) {
        window.location.assign(url);
        return Promise.resolve();
      }
      if (fragment !== this._getFragment()) {
        if (replaceState) {
          this.replaceState(fragment);
        } else {
          this.pushState(fragment);
        }
      }
      return this._handleLocationChange(url);
    },

    /**
     * @param {String|Null} [url] Absolute or relative URL
     */
    pushState: function(url) {
      this.hrefInitialIgnore = null;
      window.history.pushState(null, null, url);
    },

    /**
     * @param {String|Null} [url] Absolute or relative URL
     */
    replaceState: function(url) {
      this.hrefInitialIgnore = null;
      window.history.replaceState(null, null, url);
    },

    /**
     * @returns string
     */
    _getFragment: function() {
      return this._getFragmentByLocation(window.location);
    },

    /**
     * @param {String} url
     * @returns Location
     */
    _getLocationByUrl: function(url) {
      var location = document.createElement('a');
      if (url) {
        location.href = url;
      }
      return location;
    },

    /**
     * @param {Location} location
     * @returns string
     */
    _getFragmentByLocation: function(location) {
      return location.pathname + location.search + location.hash;
    },

    /**
     * @param {String} url
     * @returns string
     */
    _getFragmentByUrl: function(url) {
      return this._getFragmentByLocation(this._getLocationByUrl(url));
    },

    /**
     * @param {String} url
     * @returns {Promise}
     */
    _handleLocationChange: function(url) {
      var paramsStateNext = null;
      var pageCurrent = cm.getLayout().findPage();

      if (pageCurrent && pageCurrent.hasStateParams()) {
        var locationCurrent = this._getLocationByUrl(pageCurrent.getUrl());
        var locationNext = this._getLocationByUrl(url);

        if (locationCurrent.pathname === locationNext.pathname) {
          var paramsCurrent = cm.request.parseQueryParams(locationCurrent.search);
          var paramsNext = cm.request.parseQueryParams(locationNext.search);

          var stateParamNames = pageCurrent.getStateParams();

          var paramsNonStateCurrent = _.pick(paramsCurrent, _.difference(_.keys(paramsCurrent), stateParamNames));
          var paramsNonStateNext = _.pick(paramsNext, _.difference(_.keys(paramsNext), stateParamNames));

          if (_.isEqual(paramsNonStateCurrent, paramsNonStateNext)) {
            paramsStateNext = _.pick(paramsNext, _.intersection(_.keys(paramsNext), stateParamNames));
          }
        }
      }

      if (paramsStateNext) {
        if (false !== cm.getLayout().getPage().routeToState(paramsStateNext, url)) {
          return Promise.resolve();
        }
      }

      var urlSite = cm.getUrl();
      var urlBase = cm.options.urlBase;
      if (0 === url.indexOf(urlSite)) {
        var path = url.substr(urlBase.length);
        return cm.getDocument().loadPage(path);
      } else {
        window.location.assign(url);
        return Promise.resolve();
      }
    }
  },

  request: {

    /**
     * @param {Object} queryParams
     * @return {Object}
     */
    parseQueryParams: function(queryParams) {
      var params = queryString.parse(queryParams);
      var arrayParamRegex = /^(\w+)\[(\w+)]$/;
      _.each(params, function(value, key) {
        var result = arrayParamRegex.exec(key);
        if (result) {
          var paramName = result[1];
          var arrayKey = result[2];
          delete params[key];
          if (!params[paramName]) {
            params[paramName] = {};
          }
          params[paramName][arrayKey] = value;
        }
      });
      return params;
    }
  },

  userAgent: (function(ua) {
    return window.UserAgentParser.parse(ua);
  })(navigator.userAgent || '')
});
