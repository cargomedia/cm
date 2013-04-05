/*
 * Author: CM
 */
(function($) {
	var nativeSupport =  'placeholder' in document.createElement('input') && 'placeholder' in document.createElement('textarea');

	$.fn.placeholder = function() {
		if (nativeSupport) {
			return this;
		}

		var cssCopy = ['font-family', 'font-size', 'line-height'];

		return this.each(function() {
			$(this).find('input[type=text][placeholder], input[type=password][placeholder], textarea[placeholder]').each(function() {
				var $input = $(this);
				var text = $input.attr('placeholder');
				if (text) {
					$input.removeAttr('placeholder');
					var id = $input.attr('id');
					var value = $input.val();
					var $wrap = $('<div class="placeholder-wrap" />')
						.css({
							'position': 'relative'
						});
					var $label = $('<label class="placeholder" />')
						.css({
							'position': 'absolute',
							'top': 0,
							'left': 0,
							'cursor': 'text',
							'border': 'solid transparent',
							'border-width': $input.css('padding-top') + ' ' + $input.css('padding-right') + ' ' + $input.css('padding-bottom') + ' ' + $input.css('padding-left')
						})
						.text(text)
						.click(function() {
							$input.focus();
						});
					$.each(cssCopy, function(i, property) {
						$label.css(property, $input.css(property));
					});
					if (id) {
						$label.attr('for', id);
					}
					if (value) {
						$label.hide();
					}
					$input.on("focus", function() {
						$label.hide();
					});
					$input.on("blur", function() {
						if ($(this).val() == '') {
							$label.show();
						}
					});
					$(this).wrap($wrap).after($label);
				}
			});
		});
	};
})(jQuery);
