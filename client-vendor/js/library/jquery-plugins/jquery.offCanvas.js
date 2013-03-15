/*
 * Author: CM
 *
 * Dependencies: jquery.transit.js
 */
(function($) {
	var iOS = navigator.userAgent.match(/(iPad|iPhone|iPod)/i);

	var defaults = {
		delay: 500,
		easing: 'snap',
		wrap: '#offCanvas-wrap'
	};

	/**
	 * @param {jQuery} $element
	 * @param {Object} options
	 * @constructor
	 */
	var OffCanvas = function($element, options) {
		var self = this;
		this.setOptions(options);
		this.active = false;
		this.$element = $element;
		this.$element.addClass('offCanvas');
		this.$wrap = $(this.options.wrap).addClass('offCanvas-wrap');
		if (!this.$wrap.length) {
			throw 'Cannot find wrap element with selector `' + this.options.wrap + '`.';
		}
		this.$mask = this.$wrap.find('.offCanvas-mask');
		if (!this.$mask.length) {
			this.$mask = $('<div class="offCanvas-mask" />').prependTo(this.$wrap);
		}
		this.$mask.on('click', function() {
			self.close();
		});
	};

	OffCanvas.prototype = {
		options: null,
		active: null,
		$element: null,
		$wrap: null,
		$mask: null,
		setOptions: function(options) {
			this.options = $.extend({}, defaults, options || {});
		},
		toggle: function(distance, height) {
			if (this.active) {
				this.close();
			} else {
				this.open(distance, height);
			}
		},
		open: function(distance, height) {
			if (null === this.active) {
				return;
			}
			var self = this;
			this.active = null;
			$(document).scrollTop(0);
			$('html').addClass('offCanvas-active');
			this.$wrap.css({'min-height': height});
			this.$element.transition({x: distance}, this.options.delay, this.options.easing, function() {
				self.active = true;
			});
			this.$mask.show().transition({opacity: 1}, this.options.delay, this.options.easing);
		},
		close: function() {
			if (null === this.active) {
				return;
			}
			var self = this;
			this.active = null;
			this.$element.transition({x: 0}, this.options.delay, this.options.easing, function() {
				$('html').removeClass('offCanvas-active');
				self.$wrap.css({'min-height': ''});
				self.active = false;
			});
			this.$mask.transition({opacity: 0}, this.options.delay, this.options.easing, function() {
				self.$mask.hide();
			});
		}
	};

	/**
	 * @param {String|Object} action
	 * @param {Object} [options]
	 * @return {jQuery}
	 */
	$.fn.offCanvas = function(action, options) {
		return this.each(function() {
			var $self = $(this);
			var offCanvas = $self.data('offCanvas');
			if (!offCanvas) {
				offCanvas = new OffCanvas($self, action);
				$self.data('offCanvas', offCanvas);
			}

			switch (action) {
				case 'toggle':
					offCanvas.toggle(options.distance, options.height);
					break;
				case 'open':
					offCanvas.open(options.distance, options.height);
					break;
				case 'close':
					offCanvas.close();
					break;
				default:
					break;
			}
		});
	};
})(jQuery);
