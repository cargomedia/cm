/*
 * Author: CM
 */
(function($) {

	var selector = '.opener.dropdown';

	var Opener = function($element) {
		$element.on('click.opener', this.toggle);
	};

	Opener.prototype = {
		constructor: Opener,

		toggle: function(event) {
			var $opener = $(this).closest('.opener');
			$opener.find('> .window').toggleModal(function() {
				$(this).toggle();
				$opener.toggleClass('open');
			})
		}
	};

	$.fn.opener = function(option) {
		return this.each(function() {
			var $this = $(this);
			var data = $this.data('opener');
			if (!data) {
				$this.data('opener', (data = new Opener($this)))
			}
			if (typeof option == 'string') {
				data[option].call($this)
			}
		})
	};

	$(function() {
		$('body').on('click.opener', selector + ' .panel', Opener.prototype.toggle);
	});

})(jQuery);
