/*
 * Author: CM
 */
(function($) {
	var hasTouch = $('html').hasClass('touch');

	$.event.special.tapConfirm = {
		bindType: "click",
		delegateType: "click",
		handle: function(event) {
			var $this = $(this);
			if (!hasTouch || $this.hasClass('tapped')) {
				return event.handleObj.handler.call(this, event);
			}
			$this.addClass('tapped');
		}
	};
})(jQuery);
