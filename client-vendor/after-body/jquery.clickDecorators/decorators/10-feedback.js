/*
 * Author: CM
 * Dependencies: jquery.transit.js
 */
(function($) {

  $.clickDecorators.feedback = {
    isApplicable: function($element) {
      return $element.is('.button:not([data-click-confirmed])');
    },

    before: function(event) {
      var $this = $(this);
      $this.append('<span class="click-feedback" />');

      $this.find('.click-feedback').transition({
        scale: 1,
        opacity: 1,
        'border-radius': 0
      }, '200ms', 'snap', function() {
        $(this).remove('.click-feedback');
      });
    }
  };

})(jQuery);
