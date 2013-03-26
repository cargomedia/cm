/*
 * Author: CM
 */
(function($) {
	$.fn.toggleModal = function(callback) {
		callback = callback || function() { $(this).toggle(); };
		var $self = this;
		if (!$self.length) {
			return $self;
		}

		var callbackClose = function(e) {
			if (!$self.data('toggleModal')) {
				return;	// Dont close twice (eg. if toggleModalClose() was called from the same event which was triggering the close)
			}
			callback.call($self);
			$(document).removeData('toggleModal').off('.toggleModal');
			$self.removeData('toggleModal').off('.toggleModal');
		};

		if (!$self.data('toggleModal')) {
			callback.call($self);
			$(document).data('toggleModal', true);
			$self.data('toggleModal', callbackClose);
			setTimeout(function() {
				$(document).on('click.toggleModal', function(e) {
					if (!$self.length || e.target !== $self[0] && !$.contains($self[0], e.target)) {
						callbackClose();
					}
				});
				$(document).on('keydown.toggleModal', function(e) {
					if (e.which == 27) {
						callbackClose();
					}
				});
			}, 0);
		}
	};

	$.fn.toggleModalClose = function() {
		return this.each(function() {
			var callbackClose = $(this).data('toggleModal');
			if (callbackClose) {
				setTimeout(function() { callbackClose(); }, 0);
			}
		});
	};
})(jQuery);
