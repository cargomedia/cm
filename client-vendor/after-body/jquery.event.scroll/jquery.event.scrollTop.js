/*
 * Author: CM
 */
(function($) {
  var checkDelay = 100;
  var preloadMultiple = 3;

  var checkScrollHeight = function(element, event) {
    var $this = $(element);
    var innerHeight = $this.innerHeight();
    var distanceFromTop = $this.scrollTop();
    var distanceMin = innerHeight * preloadMultiple;
    if (distanceFromTop < distanceMin) {
      $(this).trigger('scrollTop', [event]);
      return true;
    }
    return false;
  };

  var handler = function(event) {
    event.type = 'scrollTop';
    var element = this;
    var data = $.data(element);

    clearTimeout(data.scrollTimeout);
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
        data.scrollStart = null;
        checkScrollHeight(element, event);
      }, checkDelay);
    }
  };

  $.event.special.scrollTop = {
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
