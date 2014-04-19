/*
 * Author: CM
 */
(function($) {
  $.fn.toggleNext = function() {
    return this.each(function() {
      var $toggler = $(this);
      var $content = $toggler.next('.toggleNext-content');

      if (!$content.length || $toggler.data('toggleNext')) {
        return;
      }

      var icon = $('<span />').addClass('icon-arrow-right');
      $toggler.prepend(icon);

      if ($toggler.hasClass('active')) {
        icon.addClass('active');
        $content.show();
      }

      $toggler.on('click.toggleNext', function() {
        var state = !$toggler.hasClass('active');
        $toggler.toggleClass('active', state);
        icon.toggleClass('active', state);
        $content.slideToggle(100, function() {
          $toggler.trigger('toggleNext', {
            state: state,
            toggler: $toggler,
            content: $content
          });
        });
      });
      $toggler.data('toggleNext', true);
    });

  };
})(jQuery);
