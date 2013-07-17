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
			var $this = $(this);

			var activateButton = function() {
				$this.addClass('confirmClick');
				$this.attr('title', $.event.special.clickConfirmed.settings.message).tooltip('enable').mouseenter();
				$this.data('timeoutId', setTimeout(function() {
					deactivateButton();
				}, 5000));
				setTimeout(function() {
					$(document).one('click.clickConfirmed', function(e) {
						if (!$this.length || e.target !== $this[0] && !$.contains($this[0], e.target)) {
							deactivateButton();
						}
					});
				}, 0);
			};

			var deactivateButton = function() {
				$this.removeClass('confirmClick');
				$this.removeAttr('title').tooltip('disable').mouseleave();
				clearTimeout($this.data('timeoutId'));
				$(document).off('click.clickConfirmed');
			}

			if ($this.hasClass('confirmClick')) {
				deactivateButton();
				return event.handleObj.handler.call(this, event);
			}
			activateButton();
		}
	};
})(jQuery);
