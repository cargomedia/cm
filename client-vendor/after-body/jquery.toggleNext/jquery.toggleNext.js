/*
 * Author: CM
 */
(function($) {
  $.fn.toggleNext = function(methodName, value) {
    return this.each(function() {
      var $toggler = $(this);
      var $content = $toggler.next('.toggleNext-content');
      var $icon = $toggler.find('.icon-arrow-right');

      if (!$content.length || ($toggler.data('toggleNext') && !methodName)) {
        return;
      }
      if ('toggle' === methodName && $toggler.data('toggleNext')) {
        return toggle(value);
      }

      $icon = $('<span />').addClass('icon-arrow-right');
      $toggler.prepend($icon);

      if ($toggler.hasClass('active')) {
        $icon.addClass('active');
        $content.show();
      }

      $toggler.on('click.toggleNext', toggle);
      $toggler.data('toggleNext', true);

      function toggle(state) {
        var currentState = $toggler.hasClass('active');
        if ('undefined' === typeof state) {
          state = !currentState;
        } else if (state === currentState) {
          return;
        }
        $toggler.toggleClass('active', state);
        $icon.toggleClass('active', state);
        $content.slideToggle(100, function() {
          var eventData = {
            toggler: $toggler,
            content: $content
          };
          $toggler.trigger('toggleNext', eventData);
          if (state) {
            $toggler.trigger('toggleNext-open', eventData);
          } else {
            $toggler.trigger('toggleNext-close', eventData);
          }
        });
      }
    });

  };
})(jQuery);
