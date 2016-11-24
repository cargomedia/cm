/*
 * Author: CM
 */

(function($) {

  /**
   * @param {Object} [lazyLoadOptions] options to lazy-load-images plugin.
   * @returns {jQuery}
   */
  $.fn.lazyImageSetup = function(lazyLoadOptions) {
    return this.each(function() {
      var options = _.defaults(lazyLoadOptions || {}, {
        offset: 600,
        attribute: 'original'
      });
      var $this = $(this);
      if ($this.closest('.scrollable').length) {
        options.container = $this.closest('.scrollable');
      }
      $this.find('img.lazy').unveil(options);
    });
  };

})(window.jQuery);
