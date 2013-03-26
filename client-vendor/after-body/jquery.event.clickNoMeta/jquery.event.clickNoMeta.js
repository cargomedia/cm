/*
 * Author: CM
 */
(function($) {
	$.event.special.clickNoMeta = {
		bindType: "click",
		delegateType: "click",
		handle: function(event) {
			if (event.ctrlKey || event.metaKey) {
				return;
			}

			return event.handleObj.handler.call(this, event);
		}
	};
})(jQuery);
