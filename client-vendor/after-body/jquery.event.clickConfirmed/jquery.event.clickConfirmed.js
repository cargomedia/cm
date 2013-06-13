/*
 * Author: CM
 */
(function($) {
	$.event.special.clickConfirmed = {
		bindType: "click",
		delegateType: "click",

		settings: {
			message: 'Please Confirm'
		},

		handle: function(event) {
			var handle = this;
			var $this = $(this);

			var activateButton = function() {
				$this.addClass('confirmClick');
				$this.attr('title', $.event.special.clickConfirmed.settings.message).tooltip('enable').mouseenter();
				handle.timeoutId = setTimeout(function() {
					deactivateButton();
				}, 5000);
			};

			var deactivateButton = function() {
				$this.removeClass('confirmClick');
				$this.removeAttr('title').tooltip('disable').mouseleave();
				clearTimeout(handle.timeoutId);
			}

			if ($this.hasClass('confirmClick')) {
				deactivateButton();
				return event.handleObj.handler.call(this, event);
			}
			activateButton();
		}
	};
})(jQuery);
