/**
 * @class CM_FormField_Geometry_Vector3
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Geometry_Vector3 = CM_FormField_Geometry_Vector2.extend({
  _class: 'CM_FormField_Geometry_Vector3',

  isEmpty: function(value) {
    return CM_FormField_Geometry_Vector2.prototype.isEmpty.call(this, value) || _.isEmpty(value.z);
  },

  /**
   * @returns {{x: *, y: *, z: *}}
   */
  getValue: function() {
    return _.extend(CM_FormField_Geometry_Vector2.prototype.getValue.call(this), {
      z: this.$('[name*=zCoordinate]').val()
    });
  },

  /**
   * @param {{x: *, y: *, z: *}} data
   */
  setValue: function(data) {
    CM_FormField_Geometry_Vector2.prototype.setValue.call(this, data);
    this.$('[name*=zCoordinate]').val(data.z);
  }
});
