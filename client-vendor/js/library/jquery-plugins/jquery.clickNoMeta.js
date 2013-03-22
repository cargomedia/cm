/*
 * Author: CM
 */
(function($) {
	$.event.special.clickNoMeta = {
		delegateType: "click",
		bindType: "click",
		handle: function(event) {
			if (event.ctrlKey || event.metaKey) {
				return;
			}

			return event.handleObj.handler.call(this, event);
		}
	};
})(jQuery);
