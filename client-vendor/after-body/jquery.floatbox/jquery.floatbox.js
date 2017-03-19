/*
 * Author: CM
 */
(function($) {
  var defaults = {
    focusElement: null,
    closable: true,
    fullscreen: false
  };

  $.floatbox = function(options) {
    this.options = $.extend({}, defaults, options || {});
  };

  var $document = $(document), $html = $('html'), $body = $('body');
  var $viewport = null;
  var scrollBackup = null;
  var lastFocusedElement = null;

  $document.on('keydown.floatbox', function(e) {
    if (e.which == 27) { // Escape
      if ($viewport && $viewport.children().length) {
        $viewport.children('.floatbox-layer:last').find('.floatbox').floatbox('close');
      }
    }
  });

  $.floatbox.fn = $.floatbox.prototype;
  $.floatbox.fn.extend = $.extend;
  $.floatbox.fn.extend({
    options: null,
    windowResizeCallback: null,
    $parent: null,
    $layer: null,
    $floatbox: null,
    setOptions: function(value) {
      if (_.isObject(value)) {
        //`value` as object to rewrite options
        _.extend(this.options, value);
        this.repaint();
      } else {
        throw new Error('Invalid param to floatbox.setOptions');
      }
    },
    show: function($element) {
      var $floatboxConfig = $element.find('.floatbox-config:first');
      this.options.fullscreen = $floatboxConfig.data('fullscreen') || this.options.fullscreen;

      this.$parent = $element.parent();
      if (!$viewport) {
        scrollBackup = {top: $document.scrollTop(), left: $document.scrollLeft()};
        $viewport = $('<div id="floatbox-viewport"/>');
        $body.append($viewport);
        $html.addClass('floatbox-active');
        $body.css({top: -scrollBackup.top, left: -scrollBackup.left});
      }
      this.$layer = $('<div class="floatbox-layer active"/>');
      var $floatboxOverlay = $('<div class="floatbox-overlay"/>');
      var $floatboxContainer = $('<div class="floatbox-container"/>');
      var $floatboxControls = $('<div class="floatbox-controls"/>');
      var $floatboxBody = $('<div class="floatbox-body"/>');
      lastFocusedElement = document.activeElement;
      if (this.options.closable) {
        $floatboxControls.append('<a class="closeFloatbox icon-close" role="button" href="javascript:;" title="' + cm.language.get("Close") + '"/>');
      }
      this.$floatbox = $('<div class="floatbox" role="dialog" aria-hidden="false" tabindex="0" />');

      if (this.options.fullscreen) {
        this.$floatbox.addClass('fullscreen');
      }

      $viewport.children('.floatbox-layer.active').removeClass('active');

      $floatboxBody.append($element.get(0));
      this.$floatbox.append($floatboxBody, $floatboxControls);
      $viewport.append(this.$layer.append($floatboxOverlay, $floatboxContainer.append(this.$floatbox)));

      var self = this;
      this.windowResizeCallback = function() {
        self.repaint.apply(self);
      };
      $(window).on('resize.floatbox', this.windowResizeCallback);
      this.repaint();

      _.defer(function() {
        self.$floatbox.addClass('fadeIn');
      });
      
      $floatboxContainer.add($floatboxOverlay).addClass('fadeIn').on('click.floatbox', function(e) {
        if (this === e.target) {
          self.close.apply(self);
        }
      });
      $floatboxControls.on('click.floatbox', '.closeFloatbox', function() {
        self.close.apply(self);
      });

      this._getFocusElement().focus();
      this.$floatbox.trap();

      this.$layer.data('floatbox', this);
      $element.triggerHandler('floatbox-open');
    },
    close: function() {
      if (!this.options.closable) {
        return;
      }
      var $element = this.$floatbox.children('.floatbox-body').children();
      if (this.$parent.length) {
        this.$parent.append($element);
      }
      this.$floatbox.triggerHandler('floatbox-close');
      this.$layer.remove();
      $viewport.children('.floatbox-layer:last').addClass('active');
      if (lastFocusedElement) {
        lastFocusedElement.focus();
      }
      lastFocusedElement = null;
      if (!$viewport.children().length) {
        $viewport.remove();
        $viewport = null;

        $html.removeClass('floatbox-active');
        window.scrollTo(scrollBackup.left, scrollBackup.top);
        $body.css({top: 0, left: 0});
      }
      $(window).off('resize.floatbox', this.windowResizeCallback);
      $element.triggerHandler('floatbox-close');

      this.$parent = null;
      this.$floatbox = null;
      this.$layer = null;
    },
    repaint: function() {
      if (this.options.fullscreen) {
        var height = $(window).height();
        this.$floatbox.css('min-height', height);
      } else {
        var top = Math.max(0, ($viewport.outerHeight(true) - this.$floatbox.outerHeight()) / 4);
        this.$floatbox.css('margin-top', top);
      }
    },
    _getFocusElement: function() {
      var focusElement = this.options.focusElement;
      var $focusElement;
      if (focusElement) {
        if ($.contains(this.$floatbox[0], focusElement)) {
          $focusElement = $(focusElement);
        } else {
          throw new Error('floatbox must contain options.focusElement.');
        }
      } else {
        $focusElement = this.$floatbox;
      }
      return $focusElement;
    }
  });

  /**
   * @param {String} methodOrOptions
   * @param {...*} args
   * @returns {jQuery}
   */
  $.fn.floatbox = function(methodOrOptions, args) {
    var method;
    if (methodOrOptions && _.isString(methodOrOptions)) {
      method = methodOrOptions;
      args = [].slice.call(arguments, 1);
    }
    return this.each(function() {
      var floatbox;
      if (method) {
        floatbox = $(this).closest('.floatbox-layer').data('floatbox');
        if (floatbox && _.isFunction(floatbox[method])) {
          floatbox[method].apply(floatbox, args);
        }
      } else {
        floatbox = new $.floatbox(methodOrOptions);
        floatbox.show($(this));
      }
    });
  };
})(jQuery);
