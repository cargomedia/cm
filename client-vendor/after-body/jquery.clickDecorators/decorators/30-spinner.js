/*
 * Author: CM
 */
(function($) {

	$.clickDecorators.spinner = {
		after: function(event, returnValue) {
			console.log('returnValue', returnValue);
			if (returnValue && _.isFunction(returnValue.promise)) {
				var $inputTarget = $(event.currentTarget).closest('button');
				var $spinner = $('<div class="spinner" />').appendTo($inputTarget);
				returnValue.always(function() {
					$spinner.remove();
				});
			}
		}
	};

})(jQuery);
