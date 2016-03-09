/*
 * Author: CM
 */
(function($) {

  /**
   * @param {jQuery} $element
   * @param {Function} [callback] fn(state)
   */
  var ToggleModal = function($element, callback) {
    callback = callback || function(state) {
        state ? $(this).show() : $(this).hide();
      };

    /** @type {jQuery} */
    this.$element = $element;
    /** @type {Function} */
    this.callback = callback;
    /** @type {Boolean} */
    this.state = false;
    /** @type {Boolean} */
    this.enabled = true;
    /** @type {ModalClose} */
    this.modalClose = new ModalClose(this.$element[0], function() {
      this.setState(false);
    }.bind(this));

  };

  /**
   * @returns {Boolean}
   */
  ToggleModal.prototype.getState = function() {
    return this.state;
  };

  /**
   * @param {Boolean} state
   */
  ToggleModal.prototype.setState = function(state) {
    if (!this.getEnabled() || state === this.getState()) {
      return;
    }
    this._executeCallback(state);
    state ? this.modalClose.enable() : this.modalClose.disable();
    this.state = state;
  };

  ToggleModal.prototype.toggle = function() {
    this.setState(!this.getState());
  };

  /**
   * @returns {Boolean}
   */
  ToggleModal.prototype.getEnabled = function() {
    return this.enabled;
  };

  /**
   * @param {Boolean} state
   */
  ToggleModal.prototype.setEnabled = function(state) {
    this.enabled = Boolean(state);
  };

  /**
   * @param {Boolean} state
   */
  ToggleModal.prototype._executeCallback = function(state) {
    this.callback.call(this.$element, state);
  };

  /**
   * @param {String|Function} [action]
   * @param {Function} [callback]
   * @return {jQuery}
   */
  $.fn.toggleModal = function(action, callback) {
    if (typeof action === 'function') {
      callback = action;
      action = 'toggle';
    }

    return this.each(function() {
      var $self = $(this);

      var toggleModal = $self.data('toggleModal');
      if (!toggleModal) {
        toggleModal = new ToggleModal($self, callback);
        $self.data('toggleModal', toggleModal);
      }

      switch (action) {
        case 'enable':
          toggleModal.setEnabled(true);
          break;
        case 'disable':
          toggleModal.setEnabled(false);
          break;
        case 'show':
        case 'open':
          toggleModal.setState(true);
          break;
        case 'hide':
        case 'close':
          toggleModal.setState(false);
          break;
        case 'toggle':
        default:
          toggleModal.toggle();
          break;
      }
    });
  };
})(jQuery);
