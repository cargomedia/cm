/*
 * Author: CM
 */
(function($) {
  /**
   * @param {jQuery} $elem
   * @constructor
   */
  function ClipSlide($elem) {
    /** @type {jQuery} */
    this.$elem = $elem;
    /** @type {jQuery} */
    this.$content = this.$elem.children();
    /** @type {jQuery} */
    this.$handle = null;

    this.$elem.addClass('clipSlide');
    this.$content.css({display: 'block'});

    var self = this;
    setTimeout(function() {
      self.toggle(false);
    }, 0);
  }

  /**
   * @param {Boolean} [state]
   */
  ClipSlide.prototype.toggle = function(state) {
    var $elem = this.$elem;
    var self = this;

    if (state) {
      if ($elem.height() < this.$content.outerHeight(true)) {
        if (!this.isEnabled()) {
          this.enable();
        }
        $elem.height($elem.height());
        $elem.css('max-height', 'none');
        $elem.animate({
          'height': this.$content.outerHeight(true)
        }, 'fast', function() {
          self.$handle.toggle(false);
          $elem.css('height', 'auto');
        });
      }
    } else {
      if ($elem.height() < this.$content.outerHeight(true)) {
        if (!this.isEnabled()) {
          this.enable();
        }
        this.$handle.toggle(true);
      } else {
        $elem.trigger('toggle.clipSlide');
      }
    }
  };

  ClipSlide.prototype.isEnabled = function() {
    return this.$elem.hasClass('clipSlide-enabled');
  };

  ClipSlide.prototype.enable = function() {
    var handleIcon = '<svg class="icon icon-arrow-down" height="16" viewBox="0 0 16 16" version="1.1" aria-hidden="true">';
    handleIcon += '<path d="M16,3.69a.8.8,0,0,1-.19.51L8.63,12.82a.8.8,0,0,1-1.23,0L.19,4.26a.8.8,0,1,1,1.22-1L8,11.07l6.57-7.89A.8.8,0,0,1,16,3.69Z"/>';
    handleIcon += '</svg>';

    this.$elem.addClass('clipSlide-enabled');
    this.$handle = $('<a href="javascript:;" class="clipSlide-handle"></a>').append(handleIcon).appendTo(this.$elem);
    var self = this;
    this.$handle.on('click.clipSlide', function() {
      self.toggle(true);
      self.$elem.trigger('toggle.clipSlide');
    });
  };

  $.fn.clipSlide = function(action, value) {
    return this.each(function() {
      var $self = $(this);
      var instance = $self.data('clipSlide');
      if (!instance) {
        instance = new ClipSlide($self);
        $self.data('clipSlide', instance);
      }

      switch (action) {
        case 'toggle':
          instance.toggle(value);
          break;
        default:
          break;
      }
    });
  };
})(jQuery);
