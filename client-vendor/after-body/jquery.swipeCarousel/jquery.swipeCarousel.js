/**
 * @author CM
 * Based on https://github.com/EightMedia/hammer.js/blob/master/tests/manual/carousel.html
 */
(function(window, $) {

  var defaults = {
    contentIdList: null
  };

  /**
   * @param {Array} array
   * @param {Number} indexOffset
   * @constructor
   */
  var CircularList = function CircularList(array, indexOffset) {
    this.array = array;
    this.indexOffset = indexOffset;
  };

  CircularList.prototype = {
    /**
     * @param {Number} index
     * @return {*}
     */
    get: function(index) {
      index = (this.indexOffset + index) % this.array.length;
      return this.array[index];
    },

    /**
     * @return {Array}
     */
    getAll: function() {
      return this.array;
    },

    /**
     * @param {Number} direction
     */
    rotate: function(direction) {
      this.indexOffset += direction;
    }
  };

  /**
   * @param {jQuery} $element
   * @param {Object} [options]
   * @constructor
   * @return {SwipeCarousel}
   */
  var SwipeCarousel = function($element, options) {
    options = _.defaults(options || {}, defaults);

    this.$element = $element;

    this.$container = $element.find('> ul');
    if (0 == this.$container.length) {
      throw new Error('Cannot find container');
    }

    var $containerChildren = this.$container.children();
    var $panelActive;

    if (options.contentIdList) {
      // Populate from contentIdList
      this.contentList = _.map(options.contentIdList, function(contentId) {
        return {'id': contentId, 'element': null};
      });

      // Add existing DOM element to contentList
      if ($containerChildren.length !== 1) {
        throw new Error('Expecting exactly one panel present.');
      }
      var $containerChild = $containerChildren.first();
      var contentId = $containerChild.data('gallery-content-id');
      if ('undefined' === typeof contentId) {
        throw new Error('Missing `gallery-content-id` data attribute');
      }
      var contentItem = this._getContentById(contentId);
      contentItem['element'] = $containerChild.children();
      $panelActive = $containerChild;

    } else {
      // Populate from DOM
      this.contentList = _.map($containerChildren, function(containerChild) {
        var $containerChild = $(containerChild);
        var contentId = $containerChild.data('gallery-content-id');
        if ('undefined' === typeof contentId) {
          contentId = null;
        }
        $panelActive = $containerChild;
        return {'id': contentId, 'element': $containerChild.children()};
      });
    }

    if (0 == this.contentList.length) {
      throw new Error('Empty contentList');
    }

    if (0 == $panelActive.prev().length) {
      $panelActive.before($('<li />'));
    }
    if (0 == $panelActive.next().length) {
      $panelActive.after($('<li />'));
    }
    var $panelList = $panelActive.prev().add($panelActive).add($panelActive.next());
    this.panelList = new CircularList($panelList.toArray(), 1);
    $panelActive.siblings().not($panelList).detach();

    this.pane_width = 0;
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

    /** @type CircularList */
    panelList: null,

    /** @type Array */
    contentList: null,

    /** @type Number */
    position: null,

    /** @type Number */
    pane_width: null,

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
      this.showPane(this.position, null, true);
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
      this.showPane(this.position + 1, eventData);
    },

    /**
     * @param {Object} [eventData]
     */
    showPrevious: function(eventData) {
      this.showPane(this.position - 1, eventData);
    },

    /**
     * @param {Number} index
     * @param {Object} [eventData]
     * @param {Boolean} [skipAnimation]
     */
    showPane: function(index, eventData, skipAnimation) {
      index = Math.max(0, Math.min(index, this.contentList.length - 1));
      eventData = eventData || {};
      var change = this.position != index;
      this.position = index;

      var offset = 13;
      this._setContainerOffset(offset, !skipAnimation);

      if (change) {
        this._onChange(eventData);
      }
    },

    /**
     * @param {*} id
     * @return Object
     */
    _getContentById: function(id) {
      var contentItem = _.findWhere(this.contentList, {'id': id});
      if (!contentItem) {
        throw new Error('Cannot find contentItem with id `' + id + '`.');
      }
      return contentItem;
    },

    _setPaneDimensions: function() {
      this.pane_width = this.$element.width() * 0.3;
      _.each(this.panelList.getAll(), function(panel) {
        $(panel).outerWidth(this.pane_width);
      }, this);
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
      return;
      var $pane_current = this.panelList.get(0);
      this.panelList.removeClass('active');
      $pane_current.addClass('active');
      _.extend(eventData, {
        index: this.position,
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
          var drag_offset = ((100 / this.pane_width) * event.gesture.deltaX);

          // slow down at the first and last pane
          if ((this.position == 0 && event.gesture.direction == 'right') || (this.position == this.contentList.length - 1 && event.gesture.direction == 'left')) {
            drag_offset *= .4;
          }

          this._setContainerOffset(drag_offset, false);
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
            this.showPane(this.position);
          }
          break;
      }
    }
  };

  /**
   * @param {String} [action]
   * @param {Object} [options]
   * @return {jQuery}
   */
  $.fn.swipeCarousel = function(action, options) {
    if ('object' === typeof action) {
      options = action;
    }

    return this.each(function() {
      var $self = $(this);
      var swipeCarousel = $self.data('swipeCarousel');

      if (!swipeCarousel) {
        swipeCarousel = new SwipeCarousel($self, options);
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
