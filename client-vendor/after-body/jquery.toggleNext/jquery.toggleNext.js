/*
 * Author: CM
 */
(function($) {

  function ToggleNext($toggler) {
    this.$toggler = $toggler;
    this.$content = $toggler.next('.toggleNext-content');

    if (this.$content.length) {
      this.initialize();
    }
  }

  ToggleNext.prototype.initialize = function() {
    this.$icon = $('<span/>').addClass('icon-arrow-right');
    this.$toggler.prepend(this.$icon);

    if (this.$toggler.hasClass('active')) {
      this.$icon.addClass('active');
      this.$content.show();
    }

    var self = this;
    this.$toggler.on('click.toggleNext', function() {
      self.toggle()
    });
    this.$toggler.data('toggleNext', true);
  };

  /**
   * @param {Boolean} [newState]
   */
  ToggleNext.prototype.toggle = function(newState) {
    var currentState = this.$toggler.hasClass('active');
    if ('undefined' === typeof newState) {
      newState = !currentState;
    } else if (newState === currentState) {
      return;
    }
    this.$toggler.toggleClass('active', newState);
    this.$icon.toggleClass('active', newState);

    var self = this;
    this.$content.slideToggle(100, function() {
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
