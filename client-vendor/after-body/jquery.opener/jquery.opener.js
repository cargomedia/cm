/*
 * Author: CM
 */
(function($) {

  var selector = '.openerDropdown';

  var OpenerDropdown = function($element) {
    this.element = $element;
  };

  OpenerDropdown.prototype = {
    constructor: OpenerDropdown,

    element: null,

    activate: function() {
      this.element.on('click.openerDropdown', this.toggle);
    },

    toggle: function() {
      var self = this;
      this.element.find('> .openerDropdown-window').toggleModal(function() {
        $(this).toggle();
        self.element.toggleClass('open');
      });
    },

    close: function() {
      this.element.find('> .openerDropdown-window').toggleModalClose();
    }
  };

  $.fn.opener = function(action) {
    return this.each(function() {
      var $this = $(this).closest('.openerDropdown');
      var data = $this.data('openerDropdown');
      if (!data) {
        $this.data('openerDropdown', (data = new OpenerDropdown($this)))
      }
      if ('string' === typeof action) {
        data[action]()
      }
    })
  };

  $(function() {
    $('body').on('click.openerDropdown', selector + ' .openerDropdown-panel', function() {
      $(this).closest('.openerDropdown').opener('toggle');
    });
  });

})(jQuery);
