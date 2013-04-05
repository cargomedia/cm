/*
 * Author: CM
 */
(function($) {
	$.fn.toggleNext = function() {
		return this.each(function() {
			var $toggler = $(this);
			var content = $toggler.next('.toggleNext-content');

			if (!content.length || $toggler.data('toggleNext')) {
				return;
			}

			var icon = $('<span />').addClass('icon-arrow-right');
			$toggler.prepend(icon);

			if ($toggler.hasClass('active')) {
				icon.addClass('active');
				content.show();
			}

			$toggler.on('click.toggleNext', function() {
				$toggler.toggleClass('active');
				icon.toggleClass('active');
				content.slideToggle(100);
			});
			$toggler.data('toggleNext', true);
		});

	};
})(jQuery);
