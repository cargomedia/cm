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

      function showTab($tab) {
        var index = $tab.index();
        $tab.addClass('active').siblings().removeClass('active');
        var $tabContent = $contentContainer.find('> *').eq(index);
        $tabContent.addClass('active').show().find(':focusable:first').focus();
        $tabContent.siblings().removeClass('active').hide();
      }

      $buttonsContainer.on('click', 'a', function(event) {
        showTab($(this).closest('.tabs > *'));
      });

      var $tabs = $buttonsContainer.find('> *');
      var $activeTab = $tabs.filter('.active');
      if (!$activeTab.length) {
        $activeTab = $tabs.first();
      }
      showTab($activeTab);
    });
  };
})(jQuery);
