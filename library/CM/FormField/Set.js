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

  getValue: function() {
    var array = this.$('input:not([disabled])[name="' + this.options.params.name + '[]"]').map(function() {
      var $this = $(this);
      if (!$this.is(':checkbox') || $this.is(':checked')) {
        return $(this).val();
      }
      return null;
    }).get();
    var value = _.compact(array);
    return value.length ? value : null;
  },

  setValue: function(value) {
    throw new CM_Exception('Not implemented');
  }
});
