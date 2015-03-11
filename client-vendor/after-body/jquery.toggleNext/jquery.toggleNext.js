/*
 * Author: CM
 */
(function($) {

  /**
   * @param {jQuery} $toggler
   * @constructor
   */
  function ToggleNext($toggler) {
    /** @type {jQuery} */
    this.$toggler = $toggler;
    /** @type {jQuery} */
    this.$content = $toggler.next('.toggleNext-content');
    /** @type {jQuery} */
    this.$icon = null;
    /** @type {Boolean} */
    this.state = false;

    if (!this.$content.length) {
      throw new Error('toggleNext must have a next sibling with css class "toggleNext-content"');
    }

    this.initialize();
  }

  ToggleNext.prototype.initialize = function() {
    this.$icon = $('<span/>').addClass('icon-arrow-right');
    this.$toggler.prepend(this.$icon);

    if (this.$toggler.hasClass('active')) {
      this.toggle(true, true);
    }

    var self = this;
    this.$toggler.on('click.toggleNext', function() {
      self.toggle()
    });
  };

  /**
   * @param {Boolean} [newState]
   * @param {Boolean} [skipAnimation]
   */
  ToggleNext.prototype.toggle = function(newState, skipAnimation) {
    if ('undefined' === typeof newState) {
      newState = !this.state;
    } else if (newState === this.state) {
      return;
    }
    this.state = newState;
    this.$toggler.toggleClass('active', newState);
    this.$icon.toggleClass('active', newState);

    var self = this;
    var duration = skipAnimation ? 0 : 100;
    this.$content.slideToggle(duration, function() {
      var eventData = {
        toggler: self.$toggler,
        content: self.$content
      };
      self.$toggler.trigger('toggleNext', eventData);
      if (newState) {
        self.$toggler.trigger('toggleNext-open', eventData);
      } else {
        self.$toggler.trigger('toggleNext-close', eventData);
      }
    });
  };

  /**
   * @param {String} [action]
   * @param {Object} [value]
   * @return {jQuery}
   */
  $.fn.toggleNext = function(action, value) {
    return this.each(function() {
      var $self = $(this);
      var instance = $self.data('toggleNext');
      if (!instance) {
        instance = new ToggleNext($self);
        $self.data('toggleNext', instance);
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
