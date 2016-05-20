/**
 * @class CM_FormField_DateTimeInterval
 * @extends CM_FormField_Abstract
 */
var CM_FormField_DateTimeInterval = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_DateTimeInterval',

  ready: function() {
    this.bindJquery(this.$('select,input'), 'change', function() {
      this.trigger('change');
    });
  },

  isEmpty: function(value) {
    return _.isEmpty(value.day) || _.isEmpty(value.month) || _.isEmpty(value.year) || _.isEmpty(value.start) || _.isEmpty(value.end);
  },

  getInput: function() {
    return this.$('select');
  },

  /**
   * @returns {{day: *, month: *, year: *, start: *, end: *}}
   */
  getValue: function() {
    return {
      day: this.$('select.day').val(),
      month: this.$('select.month').val(),
      year: this.$('select.year').val(),
      start: this.$('input.start').val(),
      end: this.$('input.end').val()
    };
  },

  /**
   * @param {{day: *, month: *, year: *, start: *, end: *}} dateTimeInterval
   */
  setValue: function(dateTimeInterval) {
    this.$('select.day').val(dateTimeInterval.day);
    this.$('select.month').val(dateTimeInterval.month);
    this.$('select.year').val(dateTimeInterval.year);
    this.$('input.start').val(dateTimeInterval.year);
    this.$('input.end').val(dateTimeInterval.year);
  }
});
