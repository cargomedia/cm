/*
 * Author: CM
 */
(function($) {
	$.fn.fancySelect = function() {
		return this.each(function() {
			var $this = $(this);
			if ($this.data('fancySelect')) {
				return;
			}

			$this.addClass('fancySelect').data('fancySelect', true);
			var $select = $this.find('select');
			var updateLabel = function() {
				var index = $select.get(0).selectedIndex;
				var label = $select.find('option').eq(index).text();

				var labelPrefix = $select.attr('data-labelPrefix');
				if (labelPrefix) {
					label = '<span class="labelPrefix">' + labelPrefix + '</span>' + label;
				}

				$this.find('.button .label').html(label);
			};
			$select.on('change', function() {
				updateLabel();
			});
			updateLabel();
		});
	};
})(jQuery);
