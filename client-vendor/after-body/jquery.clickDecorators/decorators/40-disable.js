/*
 * Author: CM
 */
(function($) {

  $.clickDecorators.spinner = {
    isApplicable: function($element) {
      return $element.data('click-disable');
    },

    after: function(event, returnValue) {
      if (returnValue && returnValue instanceof Promise && returnValue.isPending()) {
        var $inputTarget = $(event.currentTarget).closest('[data-click-disable]');
        $inputTarget.prop('disabled', true);
        returnValue.finally(function() {
          $inputTarget.prop('disabled', false);
        });
      }
    }
  };

})(jQuery);
