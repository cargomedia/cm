/*
 * Author: CM
 */
(function($) {

  var ConfirmationMonitor = function() {
  };

  ConfirmationMonitor.prototype.isActivated = function($elem) {
    return $elem.hasClass('clickConfirmed-active');
  };

  ConfirmationMonitor.prototype.activate = function($elem) {
    $elem.addClass('confirmClick');
    $elem.addClass('clickConfirmed-active');

    var self = this;
    $elem.data('confirmClick-data', {
      timeout: setTimeout(function() {
        self.deactivate($elem);
      }, 4000),
      documentClickHandler: function(e) {
        if (!$elem.length || e.target !== $elem[0] && !$.contains($elem[0], e.target)) {
          self.deactivate($elem);
        }
      }
    });

    setTimeout(function() {
      $(document).on('click.clickConfirmed', $elem.data('confirmClick-data').documentClickHandler);
    }, 0);
  };

  ConfirmationMonitor.prototype.deactivate = function($elem) {
    $elem.removeClass('confirmClick');
    $elem.removeClass('clickConfirmed-active');

    var data = $elem.data('confirmClick-data');
    clearTimeout(data.timeout);
    $(document).off('click.clickConfirmed', data.documentClickHandler);
    $elem.removeData('confirmClick-data');
  };

  var monitor = new ConfirmationMonitor();

  $.clickDecorators.confirmed = {
    isApplicable: function($element) {
      return $element.data('click-confirmed');
    },

    before: function(event) {
      var $this = $(this);

      if (!monitor.isActivated($this)) {
        monitor.activate($this);
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    },

    after: function(event) {
      var $this = $(this);

      if (monitor.isActivated($this)) {
        monitor.deactivate($this);
      }
    }
  };

})(jQuery);
