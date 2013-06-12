/*
 * Author: CM
 */
(function($) {
	$.fn.tapTwice = function() {
		return this.each(function() {
			if (!$('html').hasClass('touch')) {
				return;
			}
			var $this = $(this);
			if ($this.data('tapTwice')) {
				return;
			}
			$this.addClass('tapTwice').data('tapTwice', true);
			$this.on('click.tapTwice', function() {
				if ($this.hasClass('tapped')) {
					return;
				}
				$this.addClass('tapped');
				return false;
			});
		});
	};
})(jQuery);
