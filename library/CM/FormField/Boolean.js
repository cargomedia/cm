/**
 * @class CM_FormField_Boolean
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Boolean = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Boolean',

  events: {
    'change input': function() {
      this.trigger('change');
    }
  },

  getValue: function() {
    return this.$('input[type=checkbox]').is(':checked') ? '1' : '0';
  }
});
