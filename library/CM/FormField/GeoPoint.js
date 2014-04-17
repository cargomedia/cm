/**
 * @class CM_FormField_GeoPoint
 * @extends CM_FormField_Abstract
 */
var CM_FormField_GeoPoint = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_GeoPoint',

  isEmpty: function(value) {
    return _.isEmpty(value.latitude) || _.isEmpty(value.longitude);
  }
});
