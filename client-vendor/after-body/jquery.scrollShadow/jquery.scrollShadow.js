/**
 * @requires Underscore.js
 * @author CM
 */
(function($) {
	$.fn.scrollShadow = function(action) {
		return this.each(function() {
			var $this = $(this);

			if ($this.data('toggleShadow')) {
				if (action === 'disable') {
					$this.closest('.scrollShadow-wrapper').remove();
					$this.removeClass('scrollShadow');
					$this.data('toggleShadow', false);
				}
				return;
			}

			$this.addClass('scrollShadow');
			$this.wrap('<div class="scrollShadow-wrapper"></div>');

			function toggleShadow() {
				var scrollTop = $this.scrollTop();
				$this.toggleClass('notScrolledTop', scrollTop != 0);
				$this.toggleClass('notScrolledBottom', scrollTop != $this.prop('scrollHeight') - $this.innerHeight());
			}

			$this.scroll(_.throttle(function() {
				toggleShadow();
			}, 200));

			$(window).resize(_.debounce(function() {
				toggleShadow();
			}, 200));

			$this.data('toggleShadow', true);
		});
	};
})(jQuery);
