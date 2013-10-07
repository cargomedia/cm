/*
 * Author: CM
 */
(function($) {

	var selector = '.openerDropdown';

	var Opener = function($element) {
		$element.on('click.openerDropdown', this.toggle);
	};

	Opener.prototype = {
		constructor: Opener,

		toggle: function(event) {
			var $opener = $(this).closest('.openerDropdown');
			$opener.find('> .openerDropdown-window').toggleModal(function() {
				$(this).toggle();
				$opener.toggleClass('open');
			});
		}
	};

	$.fn.opener = function(option) {
		return this.each(function() {
			var $this = $(this);
			var data = $this.data('openerDropdown');
			if (!data) {
				$this.data('openerDropdown', (data = new Opener($this)))
			}
			if (typeof option == 'string') {
				data[option].call($this)
			}
		})
	};

	$(function() {
		$('body').on('click.openerDropdown', selector + ' .openerDropdown-panel', Opener.prototype.toggle);
	});

})(jQuery);
