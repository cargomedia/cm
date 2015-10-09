/*
 * Author: CM
 */
(function($) {

  /**
   * @param {Function} [callback] fn(state, callbackOptions)
   * @param {Object} [callbackOptions]
   * @returns {jQuery}
   */
  $.fn.toggleModal = function(callback, callbackOptions) {
    if (1 !== this.length) {
      return this;
    }
    callback = callback || function(state, callbackOptions) {
        $(this).toggle();
      };
    callbackOptions = callbackOptions || {};

    var modalClose = this.data('toggleModal');
    if (!modalClose) {
      var self = this;
      modalClose = new ModalClose(this[0], function(callbackOptions) {
        callbackOptions = callbackOptions || {};
        callback.call(self, false, callbackOptions);
      });
      this.data('toggleModal', modalClose);
    }

    if (modalClose.getEnabled()) {
      modalClose.disable();
    } else {
      modalClose.enable();
    }
    callback.call(this, modalClose.getEnabled(), callbackOptions);

    return this;
  };

  /**
   * @param {Object} [callbackOptions]
   * @returns {jQuery}
   */
  $.fn.toggleModalClose = function(callbackOptions) {
    var callbackArguments = callbackOptions ? [callbackOptions] : [];

    return this.each(function() {
      var modalClose = $(this).data('toggleModal');
      if (modalClose) {
        modalClose.close(callbackArguments);
      }
    });
  };
})(jQuery);
