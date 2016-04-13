var CM_FormField_Abstract = require('CM/FormField/Abstract');

/**
 * @class CM_FormField_GeoPoint
 * @extends CM_FormField_Abstract
 */
var CM_FormField_GeoPoint = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_GeoPoint',

  isEmpty: function(value) {
    return _.isEmpty(value.latitude) || _.isEmpty(value.longitude);
  },

  /**
   * @returns {{latitude: *, longitude: *}}
   */
  getValue: function() {
    return {
      latitude: this.$('[name*=latitude]').val(),
      longitude: this.$('[name*=longitude]').val()
    }
  },

  /**
   * @param {{latitude: *, longitude: *}} data
   */
  setValue: function(data) {
    this.$('[name*=latitude]').val(data.latitude);
    this.$('[name*=longitude]').val(data.longitude);
  }
});


module.exports = CM_FormField_GeoPoint;