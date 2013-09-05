/*
 * Author: CM
 */
(function($) {
	$.fn.tabs = function() {
		return this.each(function() {
			var $buttonsContainer = $(this);
			var $contentContainer = $buttonsContainer.next('.tabs-content');
			if (!$contentContainer.length) {
				return;
			}

			$buttonsContainer.on('click', 'a', function(event) {
				var $activeTab = $(this).closest('.tabs > *');
				var index = $activeTab.index();
				$activeTab.addClass('active').siblings().removeClass('active');
				var $activeTabContent = $contentContainer.find('> *').eq(index);
				$activeTabContent.addClass('active').show();
				$activeTabContent.siblings().removeClass('active').hide();
			});

			var $tabs = $buttonsContainer.find('> *');
			var $activeTab = $tabs.filter('.active');
			if (!$activeTab.length) {
				$activeTab = $tabs.first();
			}
			$activeTab.find('> a').click();
		});
	};
})(jQuery);
