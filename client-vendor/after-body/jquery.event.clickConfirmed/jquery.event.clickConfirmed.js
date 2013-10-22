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
				$this.attr('title', $.event.special.clickConfirmed.settings.message).tooltip({trigger: 'manual'}).tooltip('show');

				var deactivateButton = function() {
					$this.removeClass('confirmClick');
					$this.removeAttr('title').tooltip('hide');
					$this.removeData('clickConfirmed.deactivate');
					clearTimeout(deactivateTimeout);
					$(document).off('click.clickConfirmed clickConfirmed-activate', documentClickHandler);
				};

				$this.data('clickConfirmed.deactivate', deactivateButton);

				var deactivateTimeout = setTimeout(function() {
					deactivateButton();
				}, 5000);

				var documentClickHandler = function(e) {
					if (!$this.length || e.target !== $this[0] && !$.contains($this[0], e.target)) {
						deactivateButton();
					}
				};

				setTimeout(function() {
					$(document).on('click.clickConfirmed clickConfirmed-activate', documentClickHandler);
				}, 0);
			};


			if ($this.hasClass('confirmClick')) {
				$this.data('clickConfirmed.deactivate')();
				return event.handleObj.handler.call(this, event);
			} else {
				$(document).trigger('clickConfirmed-activate');
				activateButton();
				return false;
			}
		}
	};
})(jQuery);
