/*
 * Author: CM
 * Dependencies: jquery.transit.js
 */
(function($) {

  $(document).on('mousedown', '.clickFeedback', function(event) {
    var $this = $(this);

    var buttonOffset = $this.offset();
    var feedbackSize = 2 * Math.sqrt(Math.pow($this.outerWidth(), 2) + Math.pow($this.outerHeight(), 2));

    var posX = event.pageX;
    var posY = event.pageY;

    var $feedback = $('<div class="clickFeedback-ripple" />');
    $feedback.css({
      width: feedbackSize,
      height: feedbackSize,
      left: posX - buttonOffset.left - (feedbackSize / 2),
      top: posY - buttonOffset.top - (feedbackSize / 2)
    });
    $this.append($feedback);
    $feedback.transition({
      scale: 1
    }, '200ms', 'in').transition({
      opacity: 0
    }, '200ms', 'out', function() {
      $feedback.remove();
    });

  });

})(jQuery);
