/*
 * Author: CM
 */
(function($) {
	var defaults = {
		delay: 200,
		closable: true
	};

	$.floatbox = function(options) {
		this.options = $.extend({}, defaults, options || {});
	};

	var $viewport = null;
	var replaceBodyRevert = null;

	$(document).on('keydown.floatbox', function(e) {
		if (e.which == 27) { // Escape
			if ($viewport && $viewport.children().length) {
				$viewport.children('.floatbox-layer:last').floatIn();
			}
		}
	});

	$.floatbox.fn = $.floatbox.prototype;
	$.floatbox.fn.extend = $.extend;
	$.floatbox.fn.extend({
		options: null,
		windowResizeCallback: null,
		$parent: null,
		$layer: null,
		$floatbox: null,
		show: function($element) {
			this.$parent = $element.parent();
			if (!$viewport) {
				if ($('html').hasClass('no-fixed')) {
					$('html').addClass('floatbox-replaceBody');
					var backupScrollTop = $(document).scrollTop();
					var $backupBody = $('body > *:visible').detach();

					replaceBodyRevert = function() {
						$('body').prepend($backupBody);
						$(document).scrollTop(backupScrollTop);
						replaceBodyRevert = null;
					};
				}
				$viewport = $('<div id="floatbox-viewport"/>');
				$viewport.appendTo($('body'));
				/**
				 * @IE7 Use <html> instead of <body>
				 */
				$('html').addClass('floatbox-active');
			}
			this.$layer = $('<div class="floatbox-layer active"/>');
			var $overlay = $('<div class="floatbox-overlay"/>');
			var $container = $('<div class="floatbox-container"/>');
			var $controls = $('<div class="floatbox-controls"/>');
			var $body = $('<div class="floatbox-body"/>');
			if (this.options.closable) {
				$controls.append('<a class="icon-close clickable" href="javascript:;"/>');
			}
			/**
			 * @IE7 Needs to be an inline-element, so it can become a display:inline-block
			 */
			this.$floatbox = $('<span class="floatbox"/>');

			var $floatboxConfig = $element.find('.floatbox-config:first');
			var floatboxClass = $floatboxConfig.data('class');
			if (floatboxClass) {
				this.$floatbox.addClass(floatboxClass);
			}

			$viewport.children('.floatbox-layer.active').removeClass('active');

			$body.append($element.get(0));
			this.$floatbox.append($body, $controls);
			$viewport.append(this.$layer.append($overlay, $container.append(this.$floatbox)));

			if ($('html').hasClass('floatbox-replaceBody')) {
				$(document).scrollTop(1);
			}

			var self = this;
			this.windowResizeCallback = function() {
				self.adjustTop.apply(self);
			};
			$(window).on('resize.floatbox', this.windowResizeCallback);
			this.adjustTop();

			this.$floatbox.fadeTo(this.options.delay, 1, function() {
				self.$floatbox.css('opacity', 'inherit');
				$container.add($overlay).on('click.floatbox', function(e) {
					if (this === e.target) {
						self.close.apply(self);
					}
				});
				$controls.on('click.floatbox', '.icon-close', function() {
					self.close.apply(self);
				});
			});

			this.$layer.data('floatbox', this);
		},
		close: function() {
			if (!this.options.closable) {
				return;
			}
			if (this.$parent.length) {
				var $element = this.$floatbox.children('.floatbox-body').children();
				this.$parent.append($element);
			}
			this.$layer.removeData('floatbox');
			this.$layer.remove();
			$viewport.children('.floatbox-layer:last').addClass('active');
			if (!$viewport.children().length) {
				$viewport.remove();
				$viewport = null;
				if (replaceBodyRevert) {
					replaceBodyRevert();
				}
				$('html').removeClass('floatbox-active floatbox-replaceBody');
			}
			$(window).off('resize.floatbox', this.windowResizeCallback);
		},
		adjustTop: function() {
			var top = Math.max(0, ($viewport.outerHeight(true) - this.$floatbox.outerHeight(true)) / 4);
			this.$floatbox.css('margin-top', top);
		}
	});

	$.fn.floatOut = function(options) {
		return this.each(function() {
			if (!$(this).parents('.floatbox-layer').andSelf().data('floatbox')) {
				var floatbox = new $.floatbox(options);
				floatbox.show($(this));
			}
		});
	};
	$.fn.floatIn = function() {
		return this.each(function() {
			var floatbox = $(this).parents('.floatbox-layer').andSelf().data('floatbox');
			if (floatbox) {
				floatbox.close();
			}
		});
	};
})(jQuery);
