/*
 * Author: CM
 */
(function(global, $) {

  /**
   * @param {HTMLElement} elementExclude
   * @param {Function} closeCallback
   * @constructor
   */
  var ModalClose = function(elementExclude, closeCallback) {
    /** @type {HTMLElement} */
    this.elementExclude = elementExclude;
    /** @type {Function} */
    this.closeCallback = closeCallback;
    /** @type {Boolean} */
    this.enabled = null;

    this.enable();
  };

  /**
   * @returns {Boolean}
   */
  ModalClose.prototype.getEnabled = function() {
    return this.enabled;
  };

  ModalClose.prototype.enable = function() {
    if (true === this.getEnabled()) {
      return;
    }
    this._onClickReference = this._onClick.bind(this);
    this._onKeydownReference = this._onKeydown.bind(this);
    setTimeout(function() {
      document.addEventListener('click', this._onClickReference);
      document.addEventListener('keydown', this._onKeydownReference);
    }.bind(this), 0);
    this.enabled = true;
  };

  ModalClose.prototype.disable = function() {
    if (false === this.getEnabled()) {
      return;
    }
    document.removeEventListener('click', this._onClickReference);
    document.removeEventListener('keydown', this._onKeydownReference);
    this.enabled = false;
  };

  ModalClose.prototype.close = function() {
    if (this.getEnabled()) {
      this.closeCallback.call();
      this.disable();
    }
  };

  /**
   * @param {Event} event
   */
  ModalClose.prototype._onClick = function(event) {
    if (event.target !== this.elementExclude && !$.contains(this.elementExclude, event.target)) {
      this.close();
    }
  };

  /**
   * @param {Event} event
   */
  ModalClose.prototype._onKeydown = function(event) {
    if (event.which == 27) {
      this.close();
    }
  };

  if (global['ModalClose']) {
    throw new Error('ModalClose is already defined');
  }
  global['ModalClose'] = ModalClose;

})(window, jQuery);
