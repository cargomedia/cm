/*
 * Author: CM
 */
(function($) {

	$.clickDecorators.confirmed = {
		settings: {
			message: 'Please Confirm'
		},

		before: function(event) {
			var $this = $(this);

			var activateButton = function() {
				$this.addClass('confirmClick');
				$this.attr('title', $.clickDecorators.confirmed.settings.message).tooltip({trigger: 'manual'}).tooltip('show');

				var deactivateButton = function() {
					$this.removeClass('confirmClick');
					$this.removeAttr('title').tooltip('hide');
					$this.removeData('clickConfirmed.deactivate');
					clearTimeout(deactivateTimeout);
					$(document).off('click.clickConfirmed', documentClickHandler);
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
					$(document).on('click.clickConfirmed', documentClickHandler);
				}, 0);
			};

			if ($this.hasClass('confirmClick')) {
				$this.data('clickConfirmed.deactivate')();
			} else {
				activateButton();
				return false;
			}
		}
	};

})(jQuery);
