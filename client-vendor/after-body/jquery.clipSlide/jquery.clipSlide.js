/*
 * Author: CM
 */
(function($) {
	$.fn.clipSlide = function(speed) {
		var speed = speed || 'fast';
		return this.each(function() {
			var $this = $(this);
			if ($this.data('clipSlide')) {
				return;
			}

			$this.addClass('clipSlide').data('clipSlide', true);
			var $content = $this.children();
			$content.css({display: 'block'});
			if ($this.height() >= $content.outerHeight(true)) {
				return;
			}
			var $handle = $('<a href="javascript:;" class="clipSlide-handle"><div class="icon-expand"></div></a>').appendTo($this);

			$this.css({
				position: 'relative'
			});
			$handle.css({
				position: 'absolute',
				bottom: '0',
				width: '100%'
			});

			$handle.on("click.clipSlide", function() {
				$this.height($this.height());
				$this.css('max-height', 'none');
				$this.animate({
					'height': $content.outerHeight(true)
				}, speed, function() {
					$handle.remove();
					$this.css('height', 'auto');
				});
			});
		});
	};
})(jQuery);
