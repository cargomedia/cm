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

  var handler = _.throttle(function(event) {
    event.type = 'scrollTop';
    var element = this;
    checkScrollHeight(element, event);
  }, checkDelay);

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
