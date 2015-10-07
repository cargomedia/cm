/**
 * @class CM_FormField_Set
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Set = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Set',

  events: {
    'change input': function() {
      this.trigger('change');
    }
  },

  getInput: function() {
    return this.$(':checkbox[name="' + this.options.params.name + '[]"]');
  },

  getValue: function() {
    return this.getInput().filter(':enabled:checked').get().map(function(element) {
      return $(element).val();
    });
  },

  /**
   * @param {Array} values
   */
  setValue: function(values) {
    this.getInput().removeAttr('checked');
    _.each(values, function(value) {
      this.getInputByValue(value).prop('checked', 'checked');
    }, this);
  },

  /**
   * @param {*} value
   * @returns {jQuery}
   */
  getInputByValue: function(value) {
    var $input = this.getInput().filter('[value=' + value + ']');
    if (!$input.length) {
      throw new CM_Exception('Invalid value `' + value + '` for `' + this.getName() + '` form field');
    }
    return $input;
  }
});
