/*!
 * jQuery Textarea AutoSize plugin
 * Author: Javier Julio | CM-Updated
 * Licensed under the MIT license
 */
;(function ($) {

  var pluginName = "textareaAutoSize";
  var pluginDataName = "plugin_" + pluginName;

  var containsText = function (value) {
    return (value.replace(/\s/g, '').length > 0);
  };

  function Plugin(element) {
    this.element = element;
    this.$element = $(element);
    this.init();
  }

  Plugin.prototype = {
    init: function() {
      var height = this.$element.outerHeight();
      var diff = parseInt(this.$element.css('paddingBottom')) +
                 parseInt(this.$element.css('paddingTop')) || 0;
      var getScrollHeight = function(elem) {
        return elem.scrollHeight - diff;
      };
      this.scrollHeight = getScrollHeight(this.element);
      this.height = this.$element.height();

      if (containsText(this.element.value)) {
        this.$element.height(getScrollHeight(this.element));
      }

      var self = this;
      this._updateListener = _.throttle(function() {
        var $this = $(this);

        $this.css('height', 'auto');
        var scrollHeight = getScrollHeight(this);
        $this.height(scrollHeight);
        if (Math.abs(self.scrollHeight - scrollHeight) > 1) {
          self.scrollHeight = scrollHeight;
          if (Math.abs(self.height - $this.height()) > 1) {
            self.height = $this.height();
            $this.trigger('autosize:resized');
          }
        }
      }, 50);
      // keyup is required for IE to properly reset height when deleting text
      this.$element.on('input keyup autosize.update', this._updateListener);
    },
    destroy: function() {
      this.$element.off('input keyup autosize.update', this._updateListener);
    }
  };

  $.fn[pluginName] = function(method) {
    switch (method) {
      case 'destroy':
        this.each(function() {
          $.data(this, pluginDataName).destroy();
          $.removeData(this, pluginDataName);
        });
        break;
      case 'init':
      default :
        this.each(function() {
          if (!$.data(this, pluginDataName)) {
            $.data(this, pluginDataName, new Plugin(this));
          }
        });
        break;
    }
    return this;
  };

})(jQuery);
