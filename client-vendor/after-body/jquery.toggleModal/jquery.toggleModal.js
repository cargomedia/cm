/*
 * Author: CM
 */
(function($) {

  /**
   * @param {Function} callback fn(state, callbackOptions)
   * @param {Object} [callbackOptions]
   * @returns {jQuery}
   */
  $.fn.toggleModal = function(callback, callbackOptions) {
    callback = callback || function(state) {
      $(this).toggle();
    };
    callbackOptions = callbackOptions || {};
    var $self = this;
    if (!$self.length) {
      return $self;
    }

    if (!$self.data('toggleModal')) {

      var documentClick = function(e) {
        if (!$self.length || e.target !== $self[0] && !$.contains($self[0], e.target)) {
          close();
        }
      };

      var documentKeydown = function(e) {
        if (e.which == 27) {
          close();
        }
      };

      /**
       * @param {Object} [callbackOptions]
       */
      var close = function(callbackOptions) {
        callbackOptions = callbackOptions || {};
        if (!$self.data('toggleModal')) {
          return;	// Dont close twice (eg. if toggleModalClose() was called from the same event which was triggering the close)
        }
        callback.call($self, false, callbackOptions);
        $self.removeData('toggleModal').off('.toggleModal');
        $(document).off('click.toggleModal', documentClick);
        $(document).off('keydown.toggleModal', documentKeydown);
      };

      callback.call($self, true, callbackOptions);
      $self.data('toggleModal', close);
      setTimeout(function() {
        $(document).on('click.toggleModal', documentClick);
        $(document).on('keydown.toggleModal', documentKeydown);
      }, 0);
    }

    return $self;
  };

  /**
   * @param {Object} [callbackOptions]
   * @returns {jQuery}
   */
  $.fn.toggleModalClose = function(callbackOptions) {
    return this.each(function() {
      var close = $(this).data('toggleModal');
      if (close) {
        close(callbackOptions);
      }
    });
  };
})(jQuery);
