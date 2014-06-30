/*
 * Author: CM
 */
(function($) {
  var checkDelay = 100;
  var preloadMultiple = 3;

  var checkScrollHeight = function(element, event) {
    var $this = $(element);
    var scrollHeight = $this.is($(window)) ? $('body').prop('scrollHeight') : $this.prop('scrollHeight');
    var innerHeight = $this.innerHeight();
    var distanceFromBottom = scrollHeight - innerHeight - $this.scrollTop();
    var distanceMin = innerHeight * preloadMultiple;
    if (distanceFromBottom < distanceMin) {
      $(this).trigger('scrollBottom', [event]);
      return true;
    }
    return false;
  };

  var handler = _.throttle(function(event) {
    event.type = 'scrollBottom';
    var element = this;
    checkScrollHeight(element, event);
  }, checkDelay);

  $.event.special.scrollBottom = {
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
