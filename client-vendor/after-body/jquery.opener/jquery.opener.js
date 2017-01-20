/*
 * Author: CM
 */
(function($) {

  var namespace = 'openerDropdown';
  var selector = '.' + namespace;

  var OpenerDropdown = function($element) {
    this.$element = $element;
  };

  OpenerDropdown.prototype = {
    constructor: OpenerDropdown,

    toggle: function() {
      var self = this;
      this.$element.find('> ' + selector + '-window').toggleModal(function() {
        $(this).toggle();
        self.$element.toggleClass('open');
      });
    },

    close: function() {
      this.$element.find('> ' + selector + '-window').toggleModal('hide');
    }
  };

  $.fn.opener = function(action) {
    return this.each(function() {
      var $this = $(this).closest(selector);
      var data = $this.data(namespace);
      if (!data) {
        if ('close' == action) {
          return;
        }
        $this.data(namespace, (data = new OpenerDropdown($this)))
      }
      if ('string' === typeof action) {
        data[action]()
      }
    })
  };

  $(function() {
    $(document).on('click' + selector, selector + ' ' + selector + '-panel', function() {
      $(this).closest(selector).opener('toggle');
    });
  });

})(jQuery);
