/*
 * Author: CM
 */
(function($) {

  /**
   * @return {CM_View_Abstract}
   */
  $.fn.cmView = function() {
    if (1 != this.length) {
      throw new Error('Can only operate on single jQuery element')
    }
    var $element = this.first();

    var viewId = $element.closest('.CM_View_Abstract').attr('id');
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
