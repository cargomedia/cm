/*
 * Author: CM
 */
(function($) {

  $.clickDecorators = {};

  $.event.special.click.handle = function(event) {
    var $this = $(this);
    var before = [], after = [], i;

    _.each($.clickDecorators, function(decorator, name) {
      if (decorator.isApplicable($this)) {
        if (decorator.before) {
          before.push(decorator.before);
        }
        if (decorator.after) {
          after.push(decorator.after);
        }
      }
    });

    for (i = 0; i < before.length; i++) {
      var beforeValue = before[i].call(this, event);
      if (event.isImmediatePropagationStopped()) {
        return;
      }
    }

    var returnValue = event.handleObj.handler.call(this, event);

    for (i = 0; i < after.length; i++) {
      var afterValue = after[i].call(this, event, returnValue);
      if (event.isImmediatePropagationStopped()) {
        return;
      }
    }

    return returnValue;
  };
})(jQuery);
