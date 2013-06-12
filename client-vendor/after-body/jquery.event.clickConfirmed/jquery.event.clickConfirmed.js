/*
 * Author: CM
 */
(function($) {
	$.event.special.clickConfirmed = {
		bindType: "click",
		delegateType: "click",
		handle: function(event) {
			var $this = $(this);
			if ($this.hasClass('confirmClick')) {
				return event.handleObj.handler.call(this, event);
			}
			$this.addClass('confirmClick');
			$this.attr('title', cm.language.get('Please Confirm')).tooltip().mouseenter();
		}
	};
})(jQuery);
