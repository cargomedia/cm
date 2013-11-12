/**
 * @requires Underscore.js
 * @author CM
 */
(function($) {
	$.fn.scrollShadow = function() {
		return this.each(function() {
			var $this = $(this);
			$this.wrap( "<div class='scrollShadow-wrapper'></div>" );

			$(document).ready(function() {
				toggleShadow();
			});

			$this.scroll(_.throttle(function() {
				toggleShadow();
			}, 200));

			$(window).resize(_.debounce(function() {
				toggleShadow();
			}, 200));

			function toggleShadow() {
				var scrollTop = $this.scrollTop() == 0;
				$this.toggleClass('notScrolledTop', !scrollTop);

				var scrolledBottom = $this.innerHeight() + $this.scrollTop() >= $this[0].scrollHeight;
				$this.toggleClass('notScrolledBottom', !scrolledBottom);
			}
		});
	};
})(jQuery);
