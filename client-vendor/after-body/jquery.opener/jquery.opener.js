/*
 * Author: CM
 */
(function($) {

  var selector = '.openerDropdown';

  var OpenerDropdown = function($element) {
    this.$element = $element;
  };

  OpenerDropdown.prototype = {
    constructor: OpenerDropdown,

    toggle: function() {
      var self = this;
      this.$element.find('> .openerDropdown-window').toggleModal(function() {
        $(this).toggle();
        self.$element.toggleClass('open');
      });
    },

    close: function() {
      this.$element.find('> .openerDropdown-window').toggleModal('hide');
    }
  };

  $.fn.opener = function(action) {
    return this.each(function() {
      var $this = $(this).closest(selector);
      var data = $this.data('openerDropdown');
      if (!data) {
        if ('close' == action) {
          return;
        }
        $this.data('openerDropdown', (data = new OpenerDropdown($this)))
      }
      if ('string' === typeof action) {
        data[action]()
      }
    })
  };

  $(function() {
    $('body').on('click' + selector, selector + ' .openerDropdown-panel', function() {
      $(this).closest(selector).opener('toggle');
    });
  });

})(jQuery);
