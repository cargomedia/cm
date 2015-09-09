/*
 * Author: CM
 */

(function($) {

  /**
   * @param {Object} [lazyLoadOptions] options to lazy-load-images plugin.
   * @returns {jQuery}
   */
  $.fn.contentPlaceholder = function(lazyLoadOptions) {
    return this.each(function() {
      var options = _.defaults(lazyLoadOptions, {
        threshold: 600,
        failure_limit: 10
      });
      var self = this;
      $(this).find('img.lazy').lazyload(options);
    });
  };

})(window.jQuery);
