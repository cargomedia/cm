/*
 * Author: CM
 */
(function($) {

  $.clickDecorators.nometa = {
    isApplicable: function($element) {
      return $element.data('click-nometa');
    },

    before: function(event) {
      if (event.ctrlKey || event.metaKey) {
        event.stopImmediatePropagation();
      }
    }
  };

})(jQuery);
