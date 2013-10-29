/*
 * Author: CM
 */
(function($) {
	$.fn.select = function() {
		return this.each(function() {
			var $wrapper = $(this);
			var $select = $wrapper.find('select');
			var updateLabel = function() {
				var index = $select.get(0).selectedIndex;
				var label = $select.find('option').eq(index).text();
				$wrapper.find('.button .label').text(label);
			};
			$select.on('change', function() {
				updateLabel();
			});
			updateLabel();
		});
	};
})(jQuery);
