/*
 * Author: CM
 */
(function($) {
	var checkDelay = 100;
	var preloadMultiple = 3;

	var checkScrollHeight = function(element, event) {
		var $this = $(element);
		var scrollHeight = $this.is($(window)) ? $('body').prop('scrollHeight') : $this.prop('scrollHeight');
		var outerHeight = $this.outerHeight();
		var distanceFromBottom = scrollHeight - outerHeight - $this.scrollTop();
		var distanceMin = outerHeight * preloadMultiple;
		if (distanceFromBottom < distanceMin) {
			$(this).trigger('scrollEnd', [event]);
			return true;
		}
		return false;
	};

	var handler = function(event) {
		event.type = 'scrollEnd';
		var element = this;
		var data = $.data(element);

		if (data.scrollTimeout) {
			clearTimeout(data.scrollTimeout);
		}
		var now = (new Date()).getTime();
		if (!data.scrollStart) {
			data.scrollStart = now;
		}
		var startTimeout = true;
		if ((now - data.scrollStart) > checkDelay) {
			data.scrollStart = now;
			if (checkScrollHeight(element, event)) {
				startTimeout = false;
			}
		}

		if (startTimeout) {
			data.scrollTimeout = setTimeout(function() {
				data.scrollTimeout = null;
				data.scrollStart = null;
				checkScrollHeight(element, event);
			}, checkDelay);
		}
	};

	$.event.special.scrollEnd = {
		add: function(handleObj) {
			jQuery.event.add(this, 'scroll', handler);
			jQuery.event.add(this, 'touchmove', handler);
		},
		remove: function(handleObj) {
			jQuery.event.remove(this, 'scroll', handler);
			jQuery.event.remove(this, 'touchmove', handler);
		}
	};
})(jQuery);
