/*
 * Author: CM
 */
(function($) {
	$.fn.extend({
		disable: function(message) {
			$.blockUI.defaults.overlayCSS = {opacity: 0.3};
			$.blockUI.defaults.css = {};
			return this.each(function() {
				$(this).block({
					fadeIn: 50,
					message: message ? message : null
				});
			});
		},

		enable: function() {
			return this.each(function() {
				$(this).unblock({
					fadeOut: 0
				});
			});
		},

		scrollBottom: function() {
			this.scrollTop(this.prop("scrollHeight"));
		},

		scrollTo: function(target) {
			if (target.length == 0) {
				return;
			}
			this.scrollTop(target.offset().top);
		},

		changetext: function(handler) {
			var callback = function() {
				var value_old = $(this).data('value_last');
				var value_new = $(this).val();
				if (value_new != value_old) {
					$(this).data('value_last', value_new);
					return handler.call(this);
				}
			};
			this.bind("propertychange keyup input cut paste", callback);
			$(this).watch('value', callback);
		},

		nextOrFirst: function(selector) {
			if (this.next(selector).length) {
				return this.next(selector);
			} else {
				return this.siblings(selector).length > 0 ? this.siblings(selector).filter(':first') : this;
			}
		},
		prevOrLast: function(selector) {
			if (this.prev(selector).length) {
				return this.prev(selector);
			} else {
				return this.siblings(selector).length > 0 ? this.siblings(selector).filter(':last') : this;
			}
		},

		findAndSelf: function(selector) {
			return this.find(selector).add(this.filter(selector));
		},

		/**
		 * @param {String} content
		 * @param {Integer} hideDelay
		 */
		popoverInfo: function(content, hideDelay) {
			var timeout = this.data('popover-timeout');
			window.clearTimeout(timeout);

			var self = this;
			this.popover({trigger: 'manual', placement: 'bottom', content: content}).popover('show');
			if (hideDelay) {
				timeout = window.setTimeout(function() {
					self.popover('destroy');
				}, hideDelay);
				this.data('popover-timeout', timeout)
			}
		}
	});
})(jQuery);
