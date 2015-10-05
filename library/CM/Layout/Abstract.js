/**
 * @class CM_Layout_Abstract
 * @extends CM_View_Abstract
 */
var CM_Layout_Abstract = CM_View_Abstract.extend({

  /** @type String */
  _class: 'CM_Layout_Abstract',

  /** @type jQuery|Null */
  _$pagePlaceholder: null,

  /** @type PromiseThrottled|Null */
  _loadPageThrottled: null,

  /** @type {Number} timeout ID */
  _timeoutLoading: null,

  initialize: function() {
    CM_View_Abstract.prototype.initialize.call(this);

    var layout = this;
    this._loadPageThrottled = promiseThrottler(function(path) {
      layout._createPagePlaceholder();
      layout._chargeSpinnerTimeout();

      return layout.ajaxModal('loadPage', {path: path})
        .then(function(response) {
          layout._clearSpinnerTimeout();
          if (response.redirectExternal) {
            cm.router.route(response.redirectExternal);
            return;
          }
          var view = layout._injectView(response);
          var reload = (layout.getClass() != response.layoutClass);
          if (reload) {
            window.location.replace(response.url);
            return;
          }
          layout._removePagePlaceholder(view.$el);
          layout._updateHistory(path, response.url);
          layout._onPageSetup(view, response.title, response.url, response.menuEntryHashList, response.jsTracking);
          view._ready();
          return view;
        })
        .catch(function(error) {
          layout._clearSpinnerTimeout();
          if (!(error instanceof Promise.CancellationError)) {
            layout._errorPagePlaceholder(error);
            layout._onPageError();
            throw error;
          }
        });
    }, {cancelLeading: true});
  },

  /**
   * @returns {CM_View_Abstract|null}
   */
  findPage: function() {
    return this.findChild('CM_Page_Abstract');
  },

  /**
   * @returns {CM_View_Abstract}
   */
  getPage: function() {
    var page = this.findPage();
    if (!page) {
      throw new CM_Exception('Layout doesn\'t have a page');
    }
    return page;
  },

  /**
   * @param {String} path
   * @return Promise
   */
  loadPage: function(path) {
    cm.event.trigger('navigate', path);
    return this._loadPageThrottled(path);
  },

  /**
   * @param {jQuery} $el
   */
  scrollTo: function($el) {
    var pageOffsetTop = 0;
    var page = cm.findView('CM_Page_Abstract');
    if (page) {
      pageOffsetTop = page.$el.offset().top;
    }
    $(document).scrollTop($el.offset().top - pageOffsetTop);
  },

  _onPageTeardown: function() {
    $(document).scrollTop(0);
    $('.floatbox-layer').floatIn();
  },

  /**
   * @param {CM_Page_Abstract} page
   * @param {String} title
   * @param {String} url
   * @param {String[]} menuEntryHashList
   * @param {String} [jsTracking]
   */
  _onPageSetup: function(page, title, url, menuEntryHashList, jsTracking) {
    cm.window.title.setText(title);
    $('[data-menu-entry-hash]').removeClass('active');
    var menuEntrySelectors = _.map(menuEntryHashList, function(menuEntryHash) {
      return '[data-menu-entry-hash=' + menuEntryHash + ']';
    });
    $(menuEntrySelectors.join(',')).addClass('active');
    if (jsTracking) {
      new Function(jsTracking).call(this);
    }
    if (window.location.hash) {
      var hash = window.location.hash.substring(1);
      var $anchor = $('#' + hash).add('[name=' + hash + ']');
      if ($anchor.length) {
        this.scrollTo($anchor);
      }
    }
  },

  _onPageError: function() {
    $('[data-menu-entry-hash]').removeClass('active');
  },

  _createPagePlaceholder: function() {
    if (!this._$pagePlaceholder) {
      this._$pagePlaceholder = $('<div class="router-placeholder" />');
      this.getPage().replaceWithHtml(this._$pagePlaceholder);
      this._onPageTeardown();
    } else {
      this._$pagePlaceholder.removeClass('error').html('');
    }
  },

  /**
   * @returns {jQuery}
   */
  _getPagePlaceholder: function() {
    if (!this._$pagePlaceholder) {
      this._createPagePlaceholder();
    }
    return this._$pagePlaceholder;
  },

  /**
   * @param {Element|String|jQuery} el
   */
  _removePagePlaceholder: function(el) {
    this._getPagePlaceholder().replaceWith(el);
    this._$pagePlaceholder = null;
  },

  /**
   * @param {Error} error
   */
  _errorPagePlaceholder: function(error) {
    this._getPagePlaceholder().addClass('error').html('<pre>' + error.message + '</pre>');
  },

  _chargeSpinnerTimeout: function() {
    this._clearSpinnerTimeout();
    this._timeoutLoading = this.setTimeout(function() {
      this._getPagePlaceholder().html('<div class="spinner spinner-expanded" />');
    }, 750);
  },

  _clearSpinnerTimeout: function() {
    clearTimeout(this._timeoutLoading);
  },

  /**
   * @param {String} path
   * @param {String} url
   */
  _updateHistory: function(path, url) {
    var fragment = url.substr(cm.getUrl().length);
    if (path === fragment + window.location.hash) {
      fragment = path;
    }
    window.history.replaceState(null, null, fragment);
  }

});
