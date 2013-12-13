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
		var $sliderValue = field.$('.noUiSlider-value');

		$slider.noUiSlider({
			range: [field.getOption("min"), field.getOption("max")],
			start: $input.val(),
			step: field.getOption("step"),
			handles: 1,
			behaviour: 'extend-tap',
			serialization: {
				to: [ $sliderValue, 'html' ],
				resolution: 1
			}
		});

		$input.watch("disabled", function(propName, oldVal, newVal) {
			if (false == newVal) {
				$slider.removeAttr('disabled');
			} else {
				$slider.attr('disabled', 'disabled');
			}
		});

		this.on('destruct', function() {
			$input.unwatch('disabled');
		});
	}
});
