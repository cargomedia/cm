var CM_FormField_Set = require('CM/FormField/Set');

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
   * @returns {String|Null}
   */
  getValue: function() {
    if (this._isRadio()) {
      var $checked = this.getInput().filter(':checked');
      if (0 === $checked.length) {
        return null;
      } else {
        return $checked.val();
      }
    } else {
      return this.getInput().val();
    }
  },

  /**
   * @param {String|Null} value
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


module.exports = CM_FormField_Set_Select;