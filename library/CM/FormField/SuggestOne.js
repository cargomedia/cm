/**
 * @class CM_FormField_SuggestOne
 * @extends CM_FormField_Suggest
 */
var CM_FormField_SuggestOne = CM_FormField_Suggest.extend({
  _class: 'CM_FormField_SuggestOne',

  /**
   * @return {Object}
   */
  getValue: function() {
    var value = CM_FormField_Suggest.prototype.getValue.call(this);
    return value[0];
  },

  /**
   * @param {Object} value
   */
  setValue: function(value) {
    return CM_FormField_Suggest.prototype.setValue.call(this, [value]);
  }

});
