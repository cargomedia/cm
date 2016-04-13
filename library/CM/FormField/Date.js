var CM_FormField_Abstract = require('CM/FormField/Abstract');

/**
 * @class CM_FormField_Date
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Date = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Date',

  ready: function() {
    this.bindJquery(this.$('select'), 'change', function() {
      this.trigger('change');
    });
  },

  isEmpty: function(value) {
    return _.isEmpty(value.day) || _.isEmpty(value.month) || _.isEmpty(value.year);
  },

  getInput: function() {
    return this.$('select');
  },

  /**
   * @returns {{day: *, month: *, year: *}}
   */
  getValue: function() {
    return {
      day: this.$('select.day').val(),
      month: this.$('select.month').val(),
      year: this.$('select.year').val()
    };
  },

  /**
   * @param {{day: *, month: *, year: *}} date
   */
  setValue: function(date) {
    this.$('select.day').val(date.day);
    this.$('select.month').val(date.month);
    this.$('select.year').val(date.year);
  }
});


module.exports = CM_FormField_Date;