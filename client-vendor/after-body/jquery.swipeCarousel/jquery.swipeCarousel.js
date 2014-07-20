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
      return this.array[this._normalizeIndex(index)];
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
    },

    /**
     * @param {Number} index
     * @return {Number}
     */
    _normalizeIndex: function(index) {
      return (this.indexOffset + index) % this.array.length;
    }
  };

  /**
   * @param {jQuery} $element
   * @param {Object} [options]
   * @constructor
   */
  var SwipeCarousel = function($element, options) {
    options = _.defaults(options || {}, defaults);

    this.$element = $element;

    this.$container = $element.find('> ul');
    if (0 == this.$container.length) {
      throw new Error('Cannot find container');
    }

    this.panelList = new CircularList([
      {element: null, content: null},
      {element: null, content: null},
      {element: null, content: null}
    ], 1);

    var $containerChildren = this.$container.children();

    if (options.contentIdList) {
      this._constructFromContentIdList($containerChildren, options.contentIdList);
    } else {
      this._constructFromDom($containerChildren);
    }

    var $panelActive = this.panelList.get(0)['element'];
    if (null === this.panelList.get(-1)['element']) {
      this.panelList.get(-1)['element'] = $('<li />').insertBefore($panelActive);
    }
    if (null === this.panelList.get(+1)['element']) {
      this.panelList.get(+1)['element'] = $('<li />').insertAfter($panelActive);
    }

    console.log(this.position);
    console.log(this.panelList.getAll());
    console.log(this.contentList);

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
      this._renderPanels(this.position, true);
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
     * @param {Number} position
     * @param {Object} [eventData]
     * @param {Boolean} [skipAnimation]
     */
    showPane: function(position, eventData, skipAnimation) {
      position = Math.max(0, Math.min(position, this.contentList.length - 1));
      eventData = eventData || {};
      if (this.position != position) {
        this.position = position;
        this._renderPanels(position, skipAnimation);
        this._onChange(eventData);
      }
    },

    /**
     * @param {jQuery} $containerChildren
     * @param {Array} contentIdList
     */
    _constructFromContentIdList: function($containerChildren, contentIdList) {
      // Extract existing DOM element
      if ($containerChildren.length !== 1) {
        throw new Error('Expecting exactly one container child present.');
      }
      var $containerChild = $containerChildren.first();
      var contentId = $containerChild.data('gallery-content-id');
      $containerChild.removeAttr('data-gallery-content-id');
      if ('undefined' === typeof contentId) {
        throw new Error('Missing `gallery-content-id` data attribute');
      }
      var position = contentIdList.indexOf(contentId);
      if (-1 === position) {
        throw new Error('DOM content-id `' + contentId + '` is not present in contentIdList');
      }

      // Populate contentList from contentIdList
      this.contentList = _.map(contentIdList, function(contentIdListItem) {
        var element = null;
        if (contentId == contentIdListItem) {
          element = $containerChild.children()
        }
        return {id: contentIdListItem, element: element};
      });

      // Use existing DOM element as panel 0
      var panelItem = this.panelList.get(0);
      panelItem['element'] = $containerChild;
      panelItem['content'] = this.contentList[position];
      this.position = position;
    },

    /**
     * @param {jQuery} $containerChildren
     */
    _constructFromDom: function($containerChildren) {
      if ($containerChildren.length === 0) {
        throw new Error('Expecting at least one container child.');
      }

      var position = $containerChildren.filter('.active').index();
      if (-1 === position) {
        position = 0;
      }

      this.contentList = [];
      _.each($containerChildren, function(containerChild, index) {
        // Populate contentList from DOM
        var $containerChild = $(containerChild);
        var contentId = $containerChild.data('gallery-content-id');
        $containerChild.removeAttr('data-gallery-content-id');
        if ('undefined' === typeof contentId) {
          contentId = null;
        }
        this.contentList[index] = {id: contentId, element: $containerChild.children()};

        // Populate panelList from DOM
        if (Math.abs(index - position) <= 1) {
          var panelItem = this.panelList.get(index - position);
          panelItem['element'] = $containerChild;
          panelItem['content'] = this.contentList[index];
        } else {
          $containerChild.detach();
        }
      }, this);

      this.position = position;
    },

    /**
     * @param {Number} position
     * @param {Boolean} skipAnimation
     */
    _renderPanels: function(position, skipAnimation) {
      var offset = 0.13;
      this._setContainerOffset(offset, !skipAnimation);

      for (var positionOffset = -1; positionOffset <= 1; positionOffset++) {
        var content = this.contentList[position + positionOffset] || null;
        var panel = this.panelList.get(positionOffset);
        if ((content || panel['content']) && (content !== panel['content'])) {
          panel['element'].children().detach();
          if (content) {
            panel['element'].append(content['element']);
          }
        }
        panel['content'] = content;
      }
    },

    /**
     * @param {Number} direction
     */
    _movePanel: function(direction) {
      // todo - Faster panel rendering for moving +1/-1
    },

    /**
     * @param {Number} position
     * @returns Object
     */
    _getContent: function(position) {
      var contentItem = this.contentList[position];
      if (!contentItem) {
        throw new Error('Cannot find contentItem with position `' + position + '`.');
      }
      return contentItem;
    },

    /**
     * @param {Number} position
     * @return Promise
     */
    _getContentElement: function(position) {
      var contentItem = this._getContent(position);
      if (contentItem['element']) {
        return $.Deferred().resolve(contentItem['element']).promise();
      } else {
        // todo
        return $.Deferred().resolve($('<div>hello</div>')[0]).promise();
      }
    },

    _setPaneDimensions: function() {
      this.pane_width = this.$element.width() * 0.3;
      _.each(this.panelList.getAll(), function(panel) {
        $(panel).outerWidth(this.pane_width);
      }, this);
    },

    /**
     * @param {jQuery} $element
     * @param {Number} offsetRate
     * @param {Boolean} animate
     */
    _setElementOffset: function($element, offsetRate, animate) {
      $element.removeClass('animate');
      if (animate) {
        $element.addClass('animate');
      }

      if (Modernizr.csstransforms3d) {
        $element.css('transform', 'translate3d(' + (offsetRate * 100) + '%,0,0) scale3d(1,1,1)');
      } else {
        $element.css('transform', 'translate(' + (offsetRate * 100) + '%,0)');
      }
    },

    /**
     * @param {Number} offsetRate
     * @param {Boolean} animate
     */
    _setContainerOffset: function(offsetRate, animate) {
      this._setElementOffset(this.$container, offsetRate, animate);
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
          var drag_offset = ((1 / this.pane_width) * event.gesture.deltaX);

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
