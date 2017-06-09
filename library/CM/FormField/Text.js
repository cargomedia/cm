/**
 * @class CM_FormField_Text
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Text = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Text',

  /** @type String */
  _valueLast: null,

  events: {
    'blur input': function() {
      this.trigger('blur');
    },
    'focus input': function() {
      this.trigger('focus');
    },
    'change input': function() {
      this.triggerChange();
    },
    'input input': function() {
      this.triggerChange();
    }
  },

  ready: function() {
    this._valueLast = this.getValue();
  },

  /**
   * @param {String} value
   */
  setValue: function(value) {
    this.getInput().val(value);
    this._valueLast = this.getValue();
  },

  /**
   * @return {Boolean}
   */
  hasFocus: function() {
    return this.getInput().is(':focus');
  },

  triggerChange: function() {
    var valueCurrent = this.getValue();
    var valueLast = this._valueLast;
    if (valueLast !== valueCurrent) {
      this._valueLast = valueCurrent;
      this.trigger('change', {previous: valueLast, new: valueCurrent});
    }
  }
});
