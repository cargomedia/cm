/*
 * Author: CM
 */
(function($) {
  $.fn.fancySelect = function() {
    return this.each(function() {
      var $this = $(this);
      if ($this.data('fancySelect')) {
        return;
      }

      $this.addClass('fancySelect').data('fancySelect', true);
      var $select = $this.find('select');
      var updateLabel = function() {
        var index = $select.get(0).selectedIndex;
        var label = $select.find('option').eq(index).text();
        $this.find('.fancySelect-select .label').text(label);
      };
      $select.on('change fancyselect:update', function() {
        updateLabel();
      });
      updateLabel();
    });
  };
})(jQuery);
