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

  getInput: function() {
    return this.$('input[type=checkbox]');
  },

  getValue: function() {
    return this.getInput().is(':checked') ? '1' : '0';
  },

  /**
   * @param {Boolean} checked
   */
  setValue: function(checked) {
    if (checked) {
      this.getInput().attr('checked', 'checked');
    } else {
      this.getInput().removeAttr('checked');
    }
  }
});
