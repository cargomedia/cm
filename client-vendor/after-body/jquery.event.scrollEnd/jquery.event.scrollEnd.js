/*
 * Author: CM
 */
(function($) {
	$.event.special.scrollEnd = {
		delegateType: "scroll",
		bindType: "scroll",
		handle: function(event) {
			var self = this;
			var data = $.data(self);
	
			if (data.scrollTimeout) {
				clearTimeout(data.scrollTimeout);
			}
			
			data.scrollTimeout = setTimeout(function() {
				var $this = $(self);
				var scrollHeight = $this.is($(window)) ? $('body').prop('scrollHeight') : $this.prop('scrollHeight');
				var distanceFromBottom = scrollHeight - $this.outerHeight() - $this.scrollTop();
				var distanceMin = Math.max(20, Math.min(500, scrollHeight / 10));
				if (distanceFromBottom < distanceMin) {
					event.type = event.handleObj.origType;
					var ret = event.handleObj.handler.call(self, event);
					event.type = event.handleObj.type;
					return ret;	// Bug: Doesn't return to handle(), since we're in a timeout
				}
			}, 50);
		}
	};
})(jQuery);
