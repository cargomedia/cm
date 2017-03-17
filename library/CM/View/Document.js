/**
 * @class CM_View_Document
 * @extends CM_View_Abstract
 */
var CM_View_Document = CM_View_Abstract.extend({

  /** @type {String} */
  _class: 'CM_View_Document',

  /** @type {PromiseThrottled|null} */
  _loadPageThrottled: promiseThrottler(function(path) {
    return this
      .try(function() {
        var layout = this.getLayout();
        layout.createPagePlaceholder();
        layout.chargeSpinnerTimeout();
        return this.ajaxModal('loadPage', {
          path: path,
          currentLayout: layout.getClass()
        });
      })
      .finally(function() {
        this.getLayout().clearSpinnerTimeout();
      })
      .then(function(response) {
        if (response.redirectExternal) {
          window.location.replace(response.redirectExternal);
          return;
        }

        this._updateHistory(path, response.url);
        this._updateTitle(response.title);
        this._activateMenuEntries(response.menuEntryHashList);
        if (response.jsTracking) {
          this._updateTracking(response.jsTracking);
        }

        var layout = this.getLayout();
        if (response.layoutRendering) {
          layout = this._replaceLayout(response.layoutRendering);
        }

        var view = layout._injectView(response.pageRendering);
        layout.removePagePlaceholder(view.$el);
        view._ready();

        return view;
      })
      .catch(function(error) {
        if (!(error instanceof Promise.CancellationError)) {
          this.getLayout().errorPagePlaceholder(error);
          this._deactivateMenuEntries();
          throw error;
        }
      });
  }, {cancelLeading: true}),

  /**
   * @returns {CM_Layout_Abstract}
   */
  getLayout: function() {
    return this.getChild('CM_Layout_Abstract');
  },

  /**
   * @param {String} path
   * @returns {Promise}
   */
  loadPage: function(path) {
    return this._loadPageThrottled(path);
  },

  /**
   * @param {Object} response
   * @returns {CM_Layout_Abstract}
   */
  _replaceLayout: function(response) {
    var oldLayout = this.getLayout();
    var newLayout = this._injectView(response);
    oldLayout.replaceWithHtml(newLayout.$el);
    newLayout._ready();
    return newLayout;
  },

  /**
   * @param {String} requestPath
   * @param {String} responseUrl
   * @private
   */
  _updateHistory: function(requestPath, responseUrl) {
    var responseFragment = cm.router._getFragmentByUrl(responseUrl);
    if (requestPath === responseFragment + window.location.hash) {
      responseFragment = requestPath;
    }
    window.history.replaceState(null, null, responseFragment);
  },

  /**
   * @param {String} title
   * @private
   */
  _updateTitle: function(title) {
    cm.window.title.setText(title);
  },

  /**
   * @param {String[]} menuEntryHashList
   * @private
   */
  _activateMenuEntries: function(menuEntryHashList) {
    this._deactivateMenuEntries();
    var menuEntrySelectors = _.map(menuEntryHashList, function(menuEntryHash) {
      return '[data-menu-entry-hash=' + menuEntryHash + ']';
    });
    $(menuEntrySelectors.join(',')).addClass('active');
  },

  /**
   * @private
   */
  _deactivateMenuEntries: function() {
    $('[data-menu-entry-hash]').removeClass('active');
  },

  /**
   * @param {String} jsTracking
   * @private
   */
  _updateTracking: function(jsTracking) {
    new Function(jsTracking).call(this);
  }
});
