/*
 * Author: CM
 */
(function($) {

	/**
	 * @param {Function} callback fn(state)
	 * @param {Function} [callbackClose] fn(state)
	 * @param {Object} [callbackOptions]
	 * @returns {jQuery}
	 */
	$.fn.toggleModal = function(callback, callbackClose, callbackOptions) {
		var callbackOpen = callback || function(state) { $(this).toggle(); };
		callbackClose = callbackClose || callbackOpen;
		callbackOptions = callbackOptions || {};
		var $self = this;
		if (!$self.length) {
			return $self;
		}

		/**
		 * @param {Object} [callbackOptions]
		 */
		var close = function(callbackOptions) {
			callbackOptions = callbackOptions || {};
			if (!$self.data('toggleModal')) {
				return;	// Dont close twice (eg. if toggleModalClose() was called from the same event which was triggering the close)
			}
			callbackClose.call($self, false, callbackOptions);
			$(document).removeData('toggleModal').off('.toggleModal');
			$self.removeData('toggleModal').off('.toggleModal');
		};

		if (!$self.data('toggleModal')) {
			callbackOpen.call($self, true, callbackOptions);
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

	/**
	 * @param {Object} [callbackOptions]
	 * @returns {jQuery}
	 */
	$.fn.toggleModalClose = function(callbackOptions) {
		return this.each(function() {
			var close = $(this).data('toggleModal');
			if (close) {
				close(callbackOptions);
			}
		});
	};
})(jQuery);
