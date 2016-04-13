var CM_FormField_Suggest = require('CM/FormField/Suggest');

/**
 * @class CM_FormField_SuggestOne
 * @extends CM_FormField_Suggest
 */
var CM_FormField_SuggestOne = CM_FormField_Suggest.extend({
  _class: 'CM_FormField_SuggestOne',

  /**
   * @return {Object|Null}
   */
  getValue: function() {
    var value = CM_FormField_Suggest.prototype.getValue.call(this);
    if (value.length === 0) {
      return null;
    }
    return value[0];
  },

  /**
   * @param {Object|Null} value
   */
  setValue: function(value) {
    var valueArray = [];
    if (null !== value) {
      valueArray.push(value);
    }
    return CM_FormField_Suggest.prototype.setValue.call(this, valueArray);
  }

});


module.exports = CM_FormField_SuggestOne;