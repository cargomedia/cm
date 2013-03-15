/*
 * Author: CM
 */
(function($) {
	var $hint;
	var animationTimeout;
	$.windowHint = function(content) {
		clearTimeout(animationTimeout);
		$hint = $('#windowHint').stop(true, true).hide();
		if (!$hint.length) {
			$hint = $('<div id="windowHint"><div class="windowHint-content"></div></div>').hide().prependTo('body');
		}
		$hint.find('.windowHint-content').html(content);
		$hint.slideDown(100);
		animationTimeout = setTimeout(function() {
			$hint.slideUp(500);
		}, 4000);
	};
})(jQuery);
