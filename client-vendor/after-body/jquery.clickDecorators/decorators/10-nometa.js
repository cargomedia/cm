/*
 * Author: CM
 */
(function($) {

	$.clickDecorators.nometa = {
		before: function(event) {
			if (event.ctrlKey || event.metaKey) {
				return true;
			}
		}
	};

})(jQuery);
