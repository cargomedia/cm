/**
 * @author CM
 * Based on https://github.com/EightMedia/hammer.js/blob/master/tests/manual/carousel.html
 *
 * Requires:
 * - Underscore.js
 * - jquery.Transit
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
      this.showRotate(+1, eventData);
    },

    /**
     * @param {Object} [eventData]
     */
    showPrevious: function(eventData) {
      this.showRotate(-1, eventData);
    },

    /**
     * @param {Number} direction
     * @param {Object} [eventData]
     */
    showRotate: function(direction, eventData) {
      if (Math.abs(direction) > 1) {
        throw new Error('Unexpected direction `' + direction + '`.');
      }
      var position = this._normalizePosition(this.position + direction);
      eventData = eventData || {};
      if (this.position != position) {
        this.position = position;
        this.panelList.rotate(direction);
        var self = this;
        this._setContainerOffset(-direction, true).done(function() {
          self._renderContentIntoPanels(position);
        });
        this._onChange(eventData);
      }
    },

    /**
     * @param {Number} position
     * @param {Object} [eventData]
     */
    showPosition: function(position, eventData) {
      position = this._normalizePosition(position);
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
    _normalizePosition: function(position) {
      return Math.max(0, Math.min(position, this.contentList.length - 1));
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

    _resetPanelPositions: function() {
      this._setContainerOffset(0, false);
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
     * @return Promise
     */
    _setElementOffset: function($element, offsetRate, animate) {
      var deferred = $.Deferred();

      $element.transition({x: (offsetRate * 100) + '%'}, {
        duration: animate ? 200 : 0,
        complete: function() {
          deferred.resolve();
        }
      });

      return deferred.promise();
    },

    /**
     * @param {Number} offsetRate (-1: panel left, 0: middle panel, 1: panel right)
     * @param {Boolean} animate
     * @return Promise
     */
    _setContainerOffset: function(offsetRate, animate) {
      offsetRate = (-1 / 3) + offsetRate * (1 / 3);
      return this._setElementOffset(this.$container, offsetRate, animate);
    },

    /**
     * @param {Object} eventData
     */
    _onChange: function(eventData) {
      _.extend(eventData, {
        index: this.position,
        element: this.panelList.get(0).content.element
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
          var drag_offset = event.gesture.deltaX / this.panelWidth;

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
            // todo check
            this._setContainerOffset(0, true);
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
