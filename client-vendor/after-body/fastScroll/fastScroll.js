(function() {

  var FastScroll = function(delay) {
    this.delay = delay || 500;
    this.enableTimer = 0;

    var self = this;
    this.scrollCallback = function() {
      self._onScroll();
    };
    window.addEventListener('scroll', this.scrollCallback, false);
  };

  FastScroll.prototype = {
    enableTimer: null,
    delay: null,
    scrollCallback: null,

    removeHoverClass: function() {
      if ('none' !== document.body.style.pointerEvents) {
        document.body.style.pointerEvents = 'none';
      }
    },

    addHoverClass: function() {
      document.body.style.pointerEvents = 'auto';
    },

    destroy: function() {
      window.removeEventListener('scroll', this.scrollCallback, false);
    },

    _onScroll: function() {
      clearTimeout(this.enableTimer);
      this.removeHoverClass();
      this.enableTimer = setTimeout(this.addHoverClass, this.delay);
    }
  };

  window.FastScroll = FastScroll;

})();
