/**
 * @class CM_Layout_Abstract
 * @extends CM_Component_Abstract
 */
var CM_Layout_Abstract = CM_Component_Abstract.extend({

  /** @type {String} */
  _class: 'CM_Layout_Abstract',

  /** @type {jQuery|null} */
  _$pagePlaceholder: null,

  /** @type {Number|null} */
  _timeoutLoading: null,

  _ready: function() {
    if (this.$('.page-placeholder').length) {
      this._$pagePlaceholder = this.$('.page-placeholder');
    }
    CM_Component_Abstract.prototype._ready.apply(this, arguments);
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
    return this.getChild('CM_Page_Abstract');
  },

  /**
   * @param {jQuery} $el
   */
  scrollTo: function($el) {
    var pageOffsetTop = 0;
    var page = this.findPage();
    if (page) {
      pageOffsetTop = page.$el.offset().top;
    }
    $(document).scrollTop($el.offset().top - pageOffsetTop);
  },

  chargeSpinnerTimeout: function() {
    this.clearSpinnerTimeout();
    this._timeoutLoading = this.setTimeout(function() {
      this.getPagePlaceholder().html('<div class="spinner spinner-expanded" />');
    }, 750);
  },

  clearSpinnerTimeout: function() {
    clearTimeout(this._timeoutLoading);
  },

  /**
   * @returns {Boolean}
   */
  hasPagePlaceholder: function() {
    return !!this._$pagePlaceholder;
  },

  /**
   * @returns {jQuery}
   */
  getPagePlaceholder: function() {
    if (!this.hasPagePlaceholder()) {
      this.createPagePlaceholder();
    }
    return this._$pagePlaceholder;
  },

  createPagePlaceholder: function() {
    if (!this.hasPagePlaceholder()) {
      this._$pagePlaceholder = $('<div class="page-placeholder" />');
      this.getPage().replaceWithHtml(this._$pagePlaceholder);
      this._onPageTeardown();
    } else {
      this._$pagePlaceholder.removeClass('error').html('');
    }
  },

  /**
   * @param {Element|String|jQuery} el
   */
  removePagePlaceholder: function(el) {
    this.getPagePlaceholder().replaceWith(el);
    this._$pagePlaceholder = null;
  },

  /**
   * @param {Error} error
   */
  errorPagePlaceholder: function(error) {
    this.getPagePlaceholder().addClass('error').html('<pre>' + error.message + '</pre>');
  },

  /**
   * @private
   */
  _onPageTeardown: function() {
    $(document).scrollTop(0);
    $('.floatbox').floatbox('close');
  }
});
