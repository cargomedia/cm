/**
 * @requires Underscore.js
 * @author CM
 */
(function($) {

	/**
	 * @param {jQuery} $element
	 * @constructor
	 */
	var ScrollShadow = function($element) {
		this.$element = $element;
		this.initialized = false;
	};

	ScrollShadow.prototype = {
		$element: null,
		initialized: null,

		init: function() {
			if (this.initialized) {
				this.updateShadow();
				return;
			}
			var self = this;

			this.$element.addClass('scrollShadow');
			this.$element.wrap('<div class="scrollShadow-wrapper"></div>');

			this.$element.on('scroll.scrollShadow', _.throttle(function() {
				self.updateShadow();
			}, 200));

			this.updateShadow();
			this.initialized = true;
		},

		destroy: function() {
			this.$element.closest('.scrollShadow-wrapper').remove();
			this.$element.removeClass('scrollShadow');
			this.$element.on('scrollShadow.scrollShadow');
			this.initialized = false;
		},

		updateShadow: function() {
			var scrollTop = this.$element.scrollTop();
			this.$element.toggleClass('notScrolledTop', scrollTop != 0);
			this.$element.toggleClass('notScrolledBottom', scrollTop != this.$element.prop('scrollHeight') - this.$element.innerHeight());
		}
	};


	/**
	 * @param {String} [action]
	 * @return {jQuery}
	 */
	$.fn.scrollShadow = function(action) {
		return this.each(function() {
			var $self = $(this);
			var scrollShadow = $self.data('scrollShadow');
			if (!scrollShadow) {
				scrollShadow = new ScrollShadow($self);
				$self.data('scrollShadow', scrollShadow);
			}

			switch (action) {
				case 'destroy':
					scrollShadow.destroy();
					break;
				default:
					scrollShadow.init();
					break;
			}
		});
	};
})(jQuery);
