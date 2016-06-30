/**
 * @class CM_FormField_Geometry_Vector2
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Geometry_Vector2 = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Geometry_Vector2',

  events: {
    'change input': function() {
      this.trigger('change');
    }
  },

  isEmpty: function(value) {
    return _.isEmpty(value.x) || _.isEmpty(value.y);
  },

  /**
   * @returns {{x: *, y: *}}
   */
  getValue: function() {
    return {
      x: this.$('[name*=xCoordinate]').val(),
      y: this.$('[name*=yCoordinate]').val()
    }
  },

  /**
   * @param {{x: *, y: *}} data
   */
  setValue: function(data) {
    this.$('[name*=xCoordinate]').val(data.x);
    this.$('[name*=yCoordinate]').val(data.y);
  }
});
