/*
 * Author: CM
 */
(function($) {

	var selector = '.openerDropdown';

	var OpenerDropdown = function($element) {
		$element.on('click.openerDropdown', this.toggle);
	};

	OpenerDropdown.prototype = {
		constructor: OpenerDropdown,

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
				$this.data('openerDropdown', (data = new OpenerDropdown($this)))
			}
			if (typeof option == 'string') {
				data[option].call($this)
			}
		})
	};

	$(function() {
		$('body').on('click.openerDropdown', selector + ' .openerDropdown-panel', OpenerDropdown.prototype.toggle);
	});

})(jQuery);
