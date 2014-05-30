/*
 * Author: CM
 */
(function($) {

  /**
   * @param {jQuery} $buttonsContainer
   * @constructor
   */
  var Tabs = function($buttonsContainer) {
    this.$buttonsContainer = $buttonsContainer;
    this.$contentContainer = this.$buttonsContainer.next('.tabs-content');
    if (!this.$contentContainer.length) {
      throw new Error('No tabs contents found');
    }

    var self = this;
    this.$buttonsContainer.on('click', 'a', function(event) {
      self.showTab($(this).closest('.tabs > *'));
    });

    var $tabs = this.$buttonsContainer.find('> *');
    var $activeTab = $tabs.filter('.active');
    if (!$activeTab.length) {
      $activeTab = $tabs.first();
    }
    this.showTab($activeTab, true);
  };

  Tabs.prototype = {
    $buttonsContainer: null,
    $contentContainer: null,

    showTabByName: function(tab) {
      var $tab = this.$buttonsContainer.children('[data-tab="' + tab + '"]:first');
      if (!$tab.length) {
        throw new Error('No tab with name `' + tab + '` found');
      }
      this.showTab($tab);
    },

    /**
     * @param {jQuery} $tab
     * @param {Boolean} [skipChangeEvent]
     */
    showTab: function($tab, skipChangeEvent) {
      var index = $tab.index();
      $tab.addClass('active').siblings().removeClass('active');
      var $tabContent = this.$contentContainer.find('> *').eq(index);
      $tabContent.addClass('active').show();
      $tabContent.siblings().removeClass('active').hide();
      if (!skipChangeEvent) {
        $tab.trigger('tabs.changed', {
          content: $tabContent
        });
      }
    }
  };

  /**
   * @param {String} [tab]
   * @return {jQuery}
   */
  $.fn.tabs = function(tab) {
    return this.each(function() {
      var $self = $(this);
      var tabs = $self.data('tabs');

      if (!tabs) {
        tabs = new Tabs($self);
        $self.data('tabs', tabs);
      }

      if (tab) {
        tabs.showTabByName(tab);
      }
    });
  };
})(jQuery);
