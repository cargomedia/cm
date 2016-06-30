/*
 * Author: CM
 */
(function($) {

  $.clickDecorators.spinner = {
    isApplicable: function($element) {
      return $element.data('click-spinner');
    },

    after: function(event, returnValue) {
      if (returnValue && returnValue instanceof Promise && returnValue.isPending()) {
        var $inputTarget = $(event.currentTarget).closest('[data-click-spinner]');
        $inputTarget.addClass('hasSpinner').prop('disabled', true).find('.spinner').remove();
        var $spinner = $('<div class="spinner" />').appendTo($inputTarget);
        returnValue.finally(function() {
          $inputTarget.prop('disabled', false).removeClass('hasSpinner');
          $spinner.remove();
        });
      }
    }
  };

})(jQuery);
