/**
 * @class CM_FormField_ColorPalette
 * @extends CM_FormField_Abstract
 */
var CM_FormField_ColorPalette = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_ColorPalette',

  events: {
    'click .setValueFromPalette': function(event) {
      var value = $(event.currentTarget).data('value');
      this.setValue(value);
    }
  },

  /**
   * @param {String} value
   */
  setValue: function(value) {
    var $paletteItem = this.$('.palette-item[data-value="' + value + '"]');
    this.$('.palette-item').not($paletteItem).removeClass('selected');
    $paletteItem.addClass('selected');

    CM_FormField_Abstract.prototype.setValue.call(this, value);

    this.trigger('change');
  }

});
