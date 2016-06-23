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
    return _.isEmpty(value.xCoordinate) || _.isEmpty(value.yCoordinate);
  },

  /**
   * @returns {{xCoordinate: *, yCoordinate: *}}
   */
  getValue: function() {
    return {
      xCoordinate: this.$('[name*=xCoordinate]').val(),
      yCoordinate: this.$('[name*=yCoordinate]').val()
    }
  },

  /**
   * @param {{xCoordinate: *, yCoordinate: *}} data
   */
  setValue: function(data) {
    this.$('[name*=xCoordinate]').val(data.xCoordinate);
    this.$('[name*=yCoordinate]').val(data.yCoordinate);
  }
});
