/*
 * Author: CM
 */
(function($) {
	$.clickDecorators = {};

	$.event.special.click.handle = function(event) {
		var $this = $(this);
		var before = [], after = [], i;

		_.each($.clickDecorators, function(decorator, name) {
			if ($this.data('click-' + name)) {
				if (decorator.before) {
					before.push(decorator.before);
				}
				if (decorator.after) {
					after.push(decorator.after);
				}
			}
		});

		for (i = 0; i < before.length; i++) {
			if (false === before[i].call(this, event)) {
				return false;
			}
		}

		var returnValue = event.handleObj.handler.call(this, event);

		for (i = 0; i < after.length; i++) {
			if (false === after[i].call(this, event, returnValue)) {
				return false;
			}
		}

		return returnValue;
	};
})(jQuery);
