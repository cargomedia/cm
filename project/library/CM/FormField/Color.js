ready: function() {
	/*
	 * mColorPicker's "replace" option cannot be set to false here (too late)
	 * Set it within mColorPicker!
	 */
	$.fn.mColorPicker.init.replace = false;
	$.fn.mColorPicker.init.allowTransparency = false;
	$.fn.mColorPicker.init.showLogo = false;
	$.fn.mColorPicker.init.enhancedSwatches = false;
	this.$('input').mColorPicker({
		"imageFolder": cm.getUrlStatic("img/jquery.mColorPicker/")
	});
	$("#mColorPickerFooter").remove();
	$("#mColorPicker").height(158);
}
