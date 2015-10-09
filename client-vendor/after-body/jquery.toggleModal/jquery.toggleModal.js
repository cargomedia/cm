/*
 * Author: CM
 */
(function($) {

  /**
   * @param {jQuery} $element
   * @param {Function} [callback] fn(state, callbackOptions)
   */
  var ToggleModal = function($element, callback) {
    callback = callback || function(state) {
        $(this).toggle();
      };

    /** @type {jQuery} */
    this.$element = $element;
    /** @type {Function} */
    this.callback = callback;
    /** @type {Boolean} */
    this.state = false;
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
   * @param {Object} [callbackOptions]
   */
  ToggleModal.prototype.setState = function(state, callbackOptions) {
    if (state === this.getState()) {
      return;
    }
    this._executeCallback(state, callbackOptions);
    state ? this.modalClose.enable() : this.modalClose.disable();
    this.state = state;
  };

  /**
   * @param {Object} [callbackOptions]
   */
  ToggleModal.prototype.toggle = function(callbackOptions) {
    this.setState(!this.getState(), callbackOptions);
  };


  /**
   * @param {Boolean} state
   * @param {Object} [callbackOptions]
   */
  ToggleModal.prototype._executeCallback = function(state, callbackOptions) {
    callbackOptions = callbackOptions || {};
    this.callback.call(this.$element, state, callbackOptions);
  };

  /**
   * @param {String|Function} [action]
   * @param {Object} [arg]
   * @return {jQuery}
   */
  $.fn.toggleModal = function(action, arg) {
    var callback, callbackOptions;
    if (typeof action === 'function') {
      callback = action;
      action = 'toggle';
    }
    if (typeof arg === 'function') {
      callback = arg;
    } else {
      callbackOptions = arg;
    }

    return this.each(function() {
      var $self = $(this);

      var toggleModal = $self.data('toggleModal');
      if (!toggleModal) {
        toggleModal = new ToggleModal($self, callback);
        $self.data('toggleModal', toggleModal);
      }

      switch (action) {
        case 'show':
        case 'open':
          toggleModal.setState(true, callbackOptions);
          break;
        case 'hide':
        case 'close':
          toggleModal.setState(false, callbackOptions);
          break;
        case 'toggle':
        default:
          toggleModal.toggle();
          break;
      }
    });
  };
})(jQuery);
