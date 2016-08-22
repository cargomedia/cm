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
        threshold: 600,
        failure_limit: 10
      });
      var $this = $(this);
      if ($this.closest('.scrollable').length) {
        options.container = $this.closest('.scrollable');
      }
      $this.find('img.lazy').lazyload(options);
    });
  };

})(window.jQuery);
