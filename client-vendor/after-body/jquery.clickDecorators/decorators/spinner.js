/*
 * Author: CM
 */
(function($) {

	$.clickDecorators.spinner = {
		after: function(event, returnValue) {
			if (returnValue && _.isFunction(returnValue.promise)) {
				var $inputTarget = $(event.currentTarget).closest('button');
				$inputTarget.addClass('hasSpinner').attr('disabled', true).find('.spinner').remove();
				var $spinner = $('<div class="spinner" />').appendTo($inputTarget);
				returnValue.always(function() {
					$inputTarget.attr('disabled', false).removeClass('hasSpinner');
					$spinner.remove();
				});
			}
		}
	};

})(jQuery);
