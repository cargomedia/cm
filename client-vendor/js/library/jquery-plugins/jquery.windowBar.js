/*
 * Author: CM
 */
(function($) {
	var $bar;
	$.windowBar = function(content, href) {
		href = href || null;

		if (!$bar) {
			$bar = $('<div id="windowBar" />').hide().append($('<a href="javascript:;" class="icon-close" style="float:right;" />')).append($('<div class="content" />')).prependTo('body');
		}
		var $content = $bar.find('.content').html(content);
		if (href) {
			$content.css({cursor: 'pointer'}).on('click', function() {
				window.location.href = href;
			});
		} else {
			$content.css({cursor: 'auto'}).off('click');
		}
		$bar.on('click', '.icon-close', function() {
			$bar.slideUp('fast');
		});
		return $bar.slideDown('fast');
	};
})(jQuery);
