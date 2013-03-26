/*
 * http://james.padolsey.com/javascript/monitoring-dom-properties/
 */

jQuery.fn.watch = function(prop, fn) {
	return this.each(function() {
		var self = this;
		var oldVal = self[prop];
		$(self).data('watchTimer-' + prop, setInterval(function() {
			if (self[prop] !== oldVal) {
				fn.call(self, prop, oldVal, self[prop]);
				oldVal = self[prop];
			}
		}, 100));
	});
};

jQuery.fn.unwatch = function(prop) {
	return this.each(function() {
		clearInterval($(this).data('watchTimer-' + prop));
	});
};
