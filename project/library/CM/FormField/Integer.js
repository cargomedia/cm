ready: function() {
	var field = this;
	var $slider = this.$(".slider");
	var $input = this.$("input");
	$slider.slider({
		value: $input.val(),
		min: field.getOption("min"),
		max: field.getOption("max"),
		step: field.getOption("step"),
		slide: function(event, ui) {
			var value = ui.value + 0;
			$input.val(value);
			$(this).children(".ui-slider-handle").text(value);
		},
		change: function(event, ui) {
			var value = ui.value + 0;
			$input.val(value);
			$(this).children(".ui-slider-handle").text(value);
		}
	});
	$slider.children(".ui-slider-handle").text($input.val());
	$input.watch("disabled", function (propName, oldVal, newVal) {
		$slider.slider("option", "disabled", newVal);
		$slider.toggleClass("disabled", newVal);
	});
	$input.changetext(function() {
		$slider.slider("option", "value", $(this).val());
	});
}
