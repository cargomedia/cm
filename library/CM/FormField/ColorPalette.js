/**
 * @class CM_FormField_ColorPalette
 * @extends CM_FormField_Abstract
 */
var CM_FormField_ColorPalette = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_ColorPalette',

  events: {
    'input input': function() {
      this.trigger('change');
    },
    'click .setValueFromPalette': function(event) {
      this.$('.palette-item').removeClass('selected');
      var $selectedItem = $(event.currentTarget);
      $selectedItem.addClass('selected');
      this.setValue($selectedItem.data('value'));
      this.trigger('change');
    }
  }
});
