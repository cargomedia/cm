/*
 * Author: CM
 */
(function($) {
  var checkDelay = 100;
  var preloadMultiple = 3;

  var checkScrollTop = _.throttle(function(event) {
    event.type = 'scrollTop';
    var $this = $(this);
    var innerHeight = $this.innerHeight();
    var distanceFromTop = $this.scrollTop();
    var distanceMin = innerHeight * preloadMultiple;
    if (distanceFromTop < distanceMin) {
      $(this).trigger('scrollTop', [event]);
      return true;
    }
    return false;
  }, checkDelay);

  $.event.special.scrollTop = {
    add: function(handleObj) {
      jQuery.event.add(this, 'scroll', checkScrollTop);
      jQuery.event.add(this, 'touchmove', checkScrollTop);
    },
    remove: function(handleObj) {
      jQuery.event.remove(this, 'scroll', checkScrollTop);
      jQuery.event.remove(this, 'touchmove', checkScrollTop);
    }
  };

  var checkScrollBottom = _.throttle(function(event) {
    event.type = 'scrollBottom';
    var $this = $(this);
    var scrollHeight = $this.is($(window)) ? $('body').prop('scrollHeight') : $this.prop('scrollHeight');
    var innerHeight = $this.innerHeight();
    var distanceFromBottom = scrollHeight - innerHeight - $this.scrollTop();
    var distanceMin = innerHeight * preloadMultiple;
    if (distanceFromBottom < distanceMin) {
      $(this).trigger('scrollBottom', [event]);
      return true;
    }
    return false;
  }, checkDelay);

  $.event.special.scrollBottom = {
    add: function(handleObj) {
      jQuery.event.add(this, 'scroll', checkScrollBottom);
      jQuery.event.add(this, 'touchmove', checkScrollBottom);
    },
    remove: function(handleObj) {
      jQuery.event.remove(this, 'scroll', checkScrollBottom);
      jQuery.event.remove(this, 'touchmove', checkScrollBottom);
    }
  };
})(jQuery);
