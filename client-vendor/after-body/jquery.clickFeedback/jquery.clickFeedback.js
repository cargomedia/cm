/*
 * Author: CM
 * Dependencies: jquery.transit.js
 */
(function($) {

  document.addEventListener('mousedown', function(event) {
    var $elem = $(event.target).closest('.clickFeedback');

    if ($elem.length) {
      var buttonOffset = $elem.offset();
      var feedbackSize = 2 * Math.sqrt(Math.pow($elem.outerWidth(), 2) + Math.pow($elem.outerHeight(), 2));

      var posX = event.pageX;
      var posY = event.pageY;

      var $feedback = $('<div class="clickFeedback-ripple" />');
      $feedback.css({
        width: feedbackSize,
        height: feedbackSize,
        left: posX - buttonOffset.left - (feedbackSize / 2),
        top: posY - buttonOffset.top - (feedbackSize / 2)
      });
      $elem.append($feedback);
      $feedback.transition({
        scale: 1
      }, '200ms', 'in').transition({
        opacity: 0
      }, '200ms', 'out', function() {
        $feedback.remove();
      });
    }
  });

})(jQuery);
