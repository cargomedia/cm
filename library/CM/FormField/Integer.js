/**
 * @class CM_FormField_Integer
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Integer = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Integer',

	ready: function() {
		var field = this;
		var $input = this.$('input');
		var $slider = this.$('.noUiSlider');
		var $sliderValue = this.$('.noUiSlider-value');

		$slider.noUiSlider({
			range: [field.getOption('min'), field.getOption('max')],
			start: $input.val(),
			step: field.getOption('step'),
			handles: 1,
			behaviour: 'extend-tap',
			serialization: {
				to: [[$input, [$sliderValue, 'html']]],
				resolution: 1
			}
		});

		$slider.find('.noUi-handle').attr('tabindex', '0');

		$input.watch('disabled', function(propName, oldVal, newVal) {
			if (false == newVal) {
				$slider.removeAttr('disabled');
				$slider.find('.noUi-handle').attr('tabindex', '0');
			} else {
				$slider.attr('disabled', 'disabled');
				$slider.find('.noUi-handle').attr('tabindex', '-1');
			}
		});

		$(window).bind('keydown.noUiSlider', function(event) {
			if ($slider.find('.noUi-handle').is(':focus')) {
				if (event.which === cm.keyCode.LEFT) {
					field.sliderDown();
				}
				if (event.which === cm.keyCode.RIGHT) {
					field.sliderUp();
				}
			}
		});

		this.on('destruct', function() {
			$input.unwatch('disabled');
			$(window).unbind('keydown.noUiSlider');
		});
	},

	sliderDown: function() {
		var value = parseInt(this.$('.noUiSlider').val());
		this.$('.noUiSlider').val(value - this.getOption('step'))
	},

	sliderUp: function() {
		var value = parseInt(this.$('.noUiSlider').val());
		this.$('.noUiSlider').val(value + this.getOption('step'))
	}
});
