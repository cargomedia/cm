/**
 * @requires Underscore.js
 * @author CM
 */
(function($) {

  /**
   * @param {jQuery} $element
   * @constructor
   */

  var iOS = !navigator.userAgent.match(/(iPad|iPhone|iPod)/i);

  var ScrollShadow = function($element) {
    this.$element = $element;
    this.initialized = false;
  };

  ScrollShadow.prototype = {
    $element: null,
    initialized: null,

    init: function() {
      if (this.initialized) {
        this.updateShadow();
        return;
      }

      var cssClass = 'scrollShadow';

      if (iOS) {
        // Fix for iOS Safari: Absolute positioned elements in combination with -webkit-overflow-scrolling: touch; Demo: http://jsfiddle.net/vfz1t4tj/4/
        cssClass += ' noShadows';
      }

      this.$element.addClass(cssClass);
      this.$element.wrap('<div class="scrollShadow-wrapper"></div>');

      var self = this;
      this.$element.on('scroll.scrollShadow', _.throttle(function() {
        self.updateShadow();
      }, 200));

      this.updateShadow();
      this.initialized = true;
    },

    destroy: function() {
      if (!this.initialized) {
        return;
      }
      this.$element.unwrap().removeClass('scrollShadow noShadows');
      this.$element.off('scroll.scrollShadow');
      this.initialized = false;
    },

    updateShadow: function() {
      var scrollTop = this.$element.scrollTop();
      this.$element.toggleClass('notScrolledTop', scrollTop != 0);
      this.$element.toggleClass('notScrolledBottom', scrollTop != this.$element.prop('scrollHeight') - this.$element.innerHeight());
    }
  };

  /**
   * @param {String} [action]
   * @return {jQuery}
   */
  $.fn.scrollShadow = function(action) {
    return this.each(function() {
      var $self = $(this);
      var scrollShadow = $self.data('scrollShadow');

      if (!scrollShadow) {
        scrollShadow = new ScrollShadow($self);
        $self.data('scrollShadow', scrollShadow);
      }

      switch (action) {
        case 'destroy':
          scrollShadow.destroy();
          break;
        default:
          scrollShadow.init();
          break;
      }
    });
  };
})(jQuery);
