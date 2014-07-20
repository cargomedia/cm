/**
 * @author CM
 * Based on https://github.com/EightMedia/hammer.js/blob/master/tests/manual/carousel.html
 */
(function(window, $) {

  var defaults = {
    contentIdList: null
  };

  /**
   * Item of content of the gallery
   *
   * @param {Number} id
   * @param {jQuery|Null} [element]
   * @constructor
   */
  var Content = function Content(id, element) {
    this.id = id;
    this.element = element || null;
  };

  /**
   * View panel (<li>) which will eventually contain Content's element
   *
   * @param {jQuery|Null} [element]
   * @param {Content|Null} [content]
   * @constructor
   */
  var Panel = function Panel(element, content) {
    this.element = element || null;
    this.content = content || null;
  };

  /**
   * Circular list of panels
   *
   * @param {Panel[]} array
   * @param {Number} indexOffset
   * @constructor
   */
  var PanelList = function PanelList(array, indexOffset) {
    this.array = array;
    this.indexOffset = indexOffset;
  };

  PanelList.prototype = {
    /**
     * @param {Number} index
     * @return {Panel}
     */
    get: function(index) {
      return this.array[this._normalizeIndex(index)];
    },

    /**
     * @return {Panel[]}
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

    this.panelList = new PanelList([new Panel(), new Panel(), new Panel()], 1);
    this.panelOffsetList = _.range(-1, 2);

    var $containerChildren = this.$container.children();

    if (options.contentIdList) {
      this._constructFromContentIdList($containerChildren, options.contentIdList);
    } else {
      this._constructFromDom($containerChildren);
    }

    var $panelActive = this.panelList.get(0).element;
    if (null === this.panelList.get(-1).element) {
      this.panelList.get(-1).element = $('<li />').insertBefore($panelActive);
    }
    if (null === this.panelList.get(+1).element) {
      this.panelList.get(+1).element = $('<li />').insertAfter($panelActive);
    }

    console.log(this.position);
    console.log(this.panelList.getAll());
    console.log(this.contentList);

    this.panelWidth = 0;
    this.hammer = new Hammer(this.$element[0], {
      dragLockToAxis: true,
      dragMinDistance: 20,
      swipeVelocityX: 0.1
    });
    _.bindAll(this, '_setPanelDimensions', '_onKeydown', '_onHammer');
    this.initialized = false;
  };

  SwipeCarousel.prototype = {
    /** @type jQuery */
    $element: null,

    /** @type jQuery */
    $container: null,

    /** @type PanelList */
    panelList: null,

    /** @type Array */
    panelOffsetList: null,

    /** @type Content[] */
    contentList: null,

    /** @type Number */
    position: null,

    /** @type Number */
    panelWidth: null,

    /** @type Hammer */
    hammer: null,

    /** @type Boolean */
    initialized: null,

    init: function() {
      if (this.initialized) {
        return;
      }

      this.$element.addClass('swipeCarousel');
      this._setPanelDimensions();
      this._renderContentIntoPanels(this.position);
      $(window).on('load resize orientationchange', this._setPanelDimensions);
      $(window).on('keydown', this._onKeydown);
      this.hammer.on('release dragleft dragright swipeleft swiperight', this._onHammer);
      this.initialized = true;
    },

    destroy: function() {
      if (!this.initialized) {
        return;
      }

      $(window).off('load resize orientationchange', this._setPanelDimensions);
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
     */
    showPane: function(position, eventData) {
      position = Math.max(0, Math.min(position, this.contentList.length - 1));
      eventData = eventData || {};
      if (this.position != position) {
        this.position = position;
        this._renderContentIntoPanels(position);
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
        return new Content(contentIdListItem, element);
      });

      // Use existing DOM element as panel 0
      var panel = this.panelList.get(0);
      panel.element = $containerChild;
      panel.content = this.contentList[position];
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
        this.contentList[index] = new Content(contentId, $containerChild.children());

        // Populate panelList from DOM
        if (Math.abs(index - position) <= 1) {
          var panel = this.panelList.get(index - position);
          panel.element = $containerChild;
          panel.content = this.contentList[index];
        } else {
          $containerChild.detach();
        }
      }, this);

      this.position = position;
    },

    /**
     * @param {Number} position
     */
    _renderContentIntoPanels: function(position) {
      _.each(this.panelOffsetList, function(positionOffset) {
        var content = this.contentList[position + positionOffset] || null;
        var panel = this.panelList.get(positionOffset);
        if ((content || panel.content) && (content !== panel.content)) {
          panel.element.children().detach();
          if (content) {
            panel.element.append(content.element);
          }
        }
        panel.content = content;
      }, this);
      this._resetPanelPositions();
    },

    /**
     * @param {Number} direction
     */
    _moveContent: function(direction) {
      // todo - Faster panel rendering for moving content +1/-1
    },

    _resetPanelPositions: function() {
      this._setElementOffset(this.$container, -1 / 3, false);
      _.each(this.panelOffsetList, function(positionOffset, index) {
        var panel = this.panelList.get(positionOffset);
        this._setElementOffset(panel.element, index, false);
        panel.element.toggleClass('active', 0 === positionOffset);
      }, this);
    },


    _setPanelDimensions: function() {
      this.panelWidth = this.$element.width();
      _.each(this.panelList.getAll(), function(panel) {
        panel.element.outerWidth(this.panelWidth);
      }, this);
    },

    /**
     * @param {Number} position
     * @returns Content
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
      var content = this._getContent(position);
      if (content.element) {
        return $.Deferred().resolve(content.element).promise();
      } else {
        // todo
        return $.Deferred().resolve($('<div>hello</div>')[0]).promise();
      }
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
          console.log(event.gesture.deltaX, (event.gesture.deltaX / this.panelWidth));
          var drag_offset = (-1 / 3) + (event.gesture.deltaX / this.panelWidth);

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
          if (Math.abs(event.gesture.deltaX) > this.panelWidth / 2) {
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
