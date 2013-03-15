jQuery.fn.selection = function(replace, move_to){
	var element = this.get(0);
	replace = replace || false;
	move_to = move_to || false;
	// Need to focus the given element to extract the selection
	element.focus();

	if (element.setSelectionRange) {
		var s = element.selectionStart;
		var e = element.selectionEnd;
		if (replace) {
			element.value = element.value.substr(0, s) + replace + element.value.substr(e, element.value.length);
			if (move_to > 0) { element.setSelectionRange(s+move_to, s+move_to); }
			return this;
		} else {
			return element.value.substr(s, (e - s));
		}
	} else {
		// IE 7+8
		var s = document.selection.createRange();
		if (replace) {
			s.text = replace;
			if (move_to > 0) {
				var m = document.selection.createRange();
				m.moveToBookmark(s.getBookmark());
				m.collapse(true);
				m.move('character', move_to);
				m.select();
			}
			return this;
		} else {
			return s.text;
		}
	}
};
