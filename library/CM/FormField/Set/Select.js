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

  getValue: function() {
    if (this.$('input[type=radio]').length) {
      return this.$('input[type=radio]:checked').val();
    } else {
      return this.getSelectValue();
    }
  }
});
