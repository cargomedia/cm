/**
 * @author CM
 * Based on https://github.com/EightMedia/hammer.js/blob/master/tests/manual/carousel.html
 */
(function(window, $) {

  /**
   * @param {jQuery} $element
   * @return {SwipeCarousel}
   */
  var SwipeCarousel = function($element) {
    this.$element = $element;
    this.$container = $element.find('>ul');
    if (0 == this.$container.length) {
      throw new Error('Cannot find carousel container');
    }
    this.$panes = $element.find('>ul>li');
    if (0 == this.$panes.length) {
      throw new Error('Cannot find carousel panes');
    }
    this.current_pane = this.$panes.filter('.active').index();
    if (-1 == this.current_pane) {
      this.current_pane = 0;
    }
    this.pane_width = 0;
    this.pane_count = this.$panes.length;
    this.hammer = new Hammer(this.$element[0], {
      dragLockToAxis: true,
      dragMinDistance: 20,
      swipeVelocityX: 0.1
    });
    _.bindAll(this, '_setPaneDimensions', '_onKeydown', '_onHammer');
    this.initialized = false;
  };

  SwipeCarousel.prototype = {
    /** @type jQuery */
    $element: null,

    /** @type jQuery */
    $container: null,

    /** @type jQuery */
    $panes: null,

    /** @type Number */
    current_pane: null,

    /** @type Number */
    pane_width: null,

    /** @type Number */
    pane_count: null,

    /** @type Hammer */
    hammer: null,

    /** @type Boolean */
    initialized: null,

    init: function() {
      if (this.initialized) {
        return;
      }

      this.$element.addClass('swipeCarousel');
      this._setPaneDimensions();
      this.showPane(this.current_pane, null, true);
      $(window).on('load resize orientationchange', this._setPaneDimensions);
      $(window).on('keydown', this._onKeydown);
      this.hammer.on('release dragleft dragright swipeleft swiperight', this._onHammer);
      this.initialized = true;
    },

    destroy: function() {
      if (!this.initialized) {
        return;
      }

      $(window).off('load resize orientationchange', this._setPaneDimensions);
      $(window).off('keydown', this._onKeydown);
      this.hammer.off('release dragleft dragright swipeleft swiperight', this._onHammer);
      this.initialized = false;
    },

    /**
     * @param {Object} [eventData]
     */
    showNext: function(eventData) {
      this.showPane(this.current_pane + 1, eventData);
    },

    /**
     * @param {Object} [eventData]
     */
    showPrevious: function(eventData) {
      this.showPane(this.current_pane - 1, eventData);
    },

    /**
     * @param {Number} index
     * @param {Object} [eventData]
     * @param {Boolean} [skipAnimation]
     */
    showPane: function(index, eventData, skipAnimation) {
      index = Math.max(0, Math.min(index, this.pane_count - 1));
      eventData = eventData || {};
      var change = this.current_pane != index;
      this.current_pane = index;

      var offset = -((100 / this.pane_count) * this.current_pane);
      this._setContainerOffset(offset, !skipAnimation);

      if (change) {
        this._onChange(eventData);
      }
    },

    _setPaneDimensions: function() {
      this.pane_width = this.$element.width();
      var self = this;
      this.$panes.each(function() {
        $(this).outerWidth(self.pane_width);
      });
      this.$container.width(this.pane_width * this.pane_count);
    },

    /**
     *
     * @param {Number} percent
     * @param {Boolean} animate
     */
    _setContainerOffset: function(percent, animate) {
      this.$container.removeClass('animate');
      if (animate) {
        this.$container.addClass('animate');
      }

      if (Modernizr.csstransforms3d) {
        this.$container.css('transform', 'translate3d(' + percent + '%,0,0) scale3d(1,1,1)');
      } else {
        this.$container.css('transform', 'translate(' + percent + '%,0)');
      }
    },

    /**
     * @param {Object} eventData
     */
    _onChange: function(eventData) {
      var $pane_current = this.$panes.eq(this.current_pane);
      this.$panes.removeClass('active');
      $pane_current.addClass('active');
      _.extend(eventData, {
        index: this.current_pane,
        element: $pane_current.get(0)
      });
      this.$element.trigger('swipeCarousel-change', eventData);
    },

    /**
     * @param {Event} event
     */
    _onKeydown: function(event) {
      if (event.which === cm.keyCode.LEFT && !$(event.target).is(':input')) {
        this.showPrevious();
      }
      if (event.which === cm.keyCode.RIGHT && !$(event.target).is(':input')) {
        this.showNext();
      }
    },

    /**
     * @param {Hammer.event} event
     */
    _onHammer: function(event) {
      // disable browser scrolling
      event.gesture.preventDefault();

      switch (event.type) {
        case 'dragright':
        case 'dragleft':
          // stick to the finger
          var pane_offset = -(100 / this.pane_count) * this.current_pane;
          var drag_offset = ((100 / this.pane_width) * event.gesture.deltaX) / this.pane_count;

          // slow down at the first and last pane
          if ((this.current_pane == 0 && event.gesture.direction == 'right') || (this.current_pane == this.pane_count - 1 && event.gesture.direction == 'left')) {
            drag_offset *= .4;
          }

          this._setContainerOffset(drag_offset + pane_offset, false);
          break;

        case 'swipeleft':
          this.showNext();
          event.gesture.stopDetect();
          break;

        case 'swiperight':
          this.showPrevious();
          event.gesture.stopDetect();
          break;

        case 'release':
          // more then 50% moved, navigate
          if (Math.abs(event.gesture.deltaX) > this.pane_width / 2) {
            if (event.gesture.direction == 'right') {
              this.showPrevious();
            } else {
              this.showNext();
            }
          } else {
            this.showPane(this.current_pane);
          }
          break;
      }
    }
  };

  /**
   * @param {String} [action]
   * @return {jQuery}
   */
  $.fn.swipeCarousel = function(action) {
    return this.each(function() {
      var $self = $(this);
      var swipeCarousel = $self.data('swipeCarousel');

      if (!swipeCarousel) {
        swipeCarousel = new SwipeCarousel($self);
        $self.data('swipeCarousel', swipeCarousel);
      }

      switch (action) {
        case 'destroy':
          swipeCarousel.destroy();
          break;
        default:
          swipeCarousel.init();
          break;
      }
    });
  };

  window.SwipeCarousel = SwipeCarousel;

})(window, jQuery);
