/*
 * Author: CM
 */
(function($) {

  /**
   * @param {String} [className]
   * @return {CM_View_Abstract}
   */
  $.fn.cmView = function(className) {
    if (1 != this.length) {
      throw new Error('Can only operate on single jQuery element')
    }
    var $element = this.first();

    className = className || 'CM_View_Abstract';
    var viewId = $element.closest('.' + className).attr('id');
    if (!viewId) {
      throw new Error('Cannot detect view-id');
    }

    var view = cm.views[viewId];
    if (!view) {
      throw new Error('Cannot find view with id `' + viewId + '`.');
    }
    return view;
  };

})(jQuery);
