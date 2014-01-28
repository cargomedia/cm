/*
 * Author: CM
 */
(function($) {

	/**
	 * @param {Function} callback
	 * @param {Function} [callbackClose]
	 * @returns {jQuery}
	 */
	$.fn.toggleModal = function(callback, callbackClose) {
		var callbackOpen = callback || function() { $(this).toggle(); };
		callbackClose = callbackClose || callbackOpen;
		var $self = this;
		if (!$self.length) {
			return $self;
		}

		var close = function() {
			if (!$self.data('toggleModal')) {
				return;	// Dont close twice (eg. if toggleModalClose() was called from the same event which was triggering the close)
			}
			callbackClose.call($self);
			$(document).removeData('toggleModal').off('.toggleModal');
			$self.removeData('toggleModal').off('.toggleModal');
		};

		if (!$self.data('toggleModal')) {
			callbackOpen.call($self);
			$(document).data('toggleModal', true);
			$self.data('toggleModal', close);
			setTimeout(function() {
				$(document).on('click.toggleModal', function(e) {
					if (!$self.length || e.target !== $self[0] && !$.contains($self[0], e.target)) {
						close();
					}
				});
				$(document).on('keydown.toggleModal', function(e) {
					if (e.which == 27) {
						close();
					}
				});
			}, 0);
		}

		return $self;
	};

	$.fn.toggleModalClose = function() {
		return this.each(function() {
			var close = $(this).data('toggleModal');
			if (close) {
				setTimeout(function() { close(); }, 0);
			}
		});
	};
})(jQuery);
