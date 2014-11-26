/**
 * @class CM_FormField_Date
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Date = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Date',

  ready: function() {
    var dateSource;
    if (this._browserHasSpinningDatePicker()) {
      this.$('.fancySelect').toggle(false).find('select').prop('disabled', true);
      dateSource = this.$('[type=date]').toggle(true);
    } else {
      this.$('[type=date]').prop('disabled', true);
      dateSource = this.$('select');
    }
    this.bindJquery(dateSource, 'change', function() {
      this.trigger('change');
    });
  },

  isEmpty: function(value) {
    return (_.isEmpty(value.day) || _.isEmpty(value.month) || _.isEmpty(value.year)) && _.isEmpty(value.date);
  },

  getInput: function() {
    if (this._browserHasSpinningDatePicker()) {
      return this.$('[type=date]');
    } else {
      return this.$('select:first');
    }
  },

  /**
   * This method is an exception when we detect user agent to offer a better UI.
   * @returns {boolean} whether the user's device has a native datepicker.
   */
  _browserHasSpinningDatePicker: function() {
    return /Android|iPhone|iPad|iPod|IEMobile/i.test(navigator.userAgent);
  }
});
