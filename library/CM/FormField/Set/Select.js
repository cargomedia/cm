/**
 * @class CM_FormField_Set_Select
 * @extends CM_FormField_Set
 */
var CM_FormField_Set_Select = CM_FormField_Set.extend({
  _class: 'CM_FormField_Set_Select',

  events: {
    'change select': function() {
      this.trigger('change');
    }
  },

  getInput: function() {
    return this.$('input, select');
  },

  /**
   * @returns {String}
   */
  getValue: function() {
    if (this._isRadio()) {
      return this.getInput().filter(':checked').val();
    } else {
      return this.getInput().val();
    }
  },

  /**
   * @param {String} value
   */
  setValue: function(value) {
    if (this._isRadio()) {
      this.getInputByValue(value).prop('checked', 'checked');
    } else {
      this.getInput().val(value);
      this.getInput().trigger('fancyselect:update');
    }
  },

  _isRadio: function() {
    return this.getInput().is('[type=radio]');
  }
});
