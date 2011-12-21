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
			$input.val(ui.value);
			$(this).children(".ui-slider-handle").text(ui.value);
		},
		change: function(event, ui) {
			$input.val(ui.value);
			$(this).children(".ui-slider-handle").text(ui.value);
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
