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
      var value = $(event.currentTarget).data('value');
      this.setValue(value);
      this.trigger('change');
    }
  }

});
