/*
 * Author: CM
 */
(function($) {

  /**
   * @param {jQuery} $elem
   * @constructor
   */
  var Confirmation = function($elem) {
    this._$elem = $elem;

    var $progressBar = $('<div class="clickConfirmed-bar" />');
    var $tooltip = $('<div class="clickConfirmed-tooltip" />');
    $tooltip.text(cm.language.get('Click again to confirm'));
    $elem.append($progressBar);
    $elem.append($tooltip);
    $progressBar[0].offsetHeight; // Trigger repaint

    if ($elem.offset().left + $elem.outerWidth() / 2 < $tooltip.outerWidth() / 2) {
      $tooltip.addClass('align-left');
    } else if (window.innerWidth < $elem.offset().left + $elem.outerWidth() + $tooltip.outerWidth() / 2) {
      $tooltip.addClass('align-right');
    } else {
      $tooltip.addClass('align-center');
    }
  };

  Confirmation.prototype.activate = function() {
    this._$elem.addClass('confirmClick');
    var self = this;
    _.delay(function() {
      self._$elem.addClass('ready'); // Make sure click-feedback has finished
    }, 400);
    this._$elem.data('confirmClick-data', this);

    this._timeout = setTimeout(function() {
      self.deactivate();
    }, 5000);

    this._documentClickHandler = function(e) {
      if (!self._$elem.length || e.target !== self._$elem[0] && !$.contains(self._$elem[0], e.target)) {
        self.deactivate();
      }
    };

    _.defer(function() {
      $(document).on('click.clickConfirmed', self._documentClickHandler);
    });
  };

  Confirmation.prototype.deactivate = function() {
    this._$elem.removeClass('confirmClick ready');
    this._$elem.removeData('confirmClick-data');
    this._$elem.find('.clickConfirmed-bar').remove();
    this._$elem.find('.clickConfirmed-tooltip').remove();

    clearTimeout(this._timeout);
    $(document).off('click.clickConfirmed', this._documentClickHandler);
  };


  $.clickDecorators.confirmed = {
    isApplicable: function($element) {
      return $element.data('click-confirmed');
    },

    before: function(event) {
      var $this = $(this);
      var confirmation = $this.data('confirmClick-data');

      if (!confirmation) {
        confirmation = new Confirmation($this);
        confirmation.activate();
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    },

    after: function(event) {
      var $this = $(this);
      var confirmation = $this.data('confirmClick-data');

      if (confirmation) {
        confirmation.deactivate();
      }
    }
  };

})(jQuery);
