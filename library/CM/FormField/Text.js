/**
 * @class CM_FormField_Text
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Text = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Text',

  /** @type Boolean */
  _skipTriggerChange: false,

  /** @type String */
  _valueLast: null,

  events: {
    'blur input, textarea': function() {
      this.trigger('blur');
    },
    'focus input, textarea': function() {
      this.trigger('focus');
    },
    'change input, textarea': function() {
      this.triggerChange();
    }
  },

  ready: function() {
    this._valueLast = this.getInput().val();
  },

  /**
   * @param {String} value
   */
  setValue: function(value) {
    this._skipTriggerChange = true;
    this.$('input, textarea').val(value);
    this._skipTriggerChange = false;
  },

  /**
   * @return {Boolean}
   */
  hasFocus: function() {
    return this.getInput().is(':focus');
  },

  triggerChange: function() {
    var valueCurrent = this.getInput().val();
    if (this._valueLast !== valueCurrent) {
      this._valueLast = valueCurrent;
      if (!this._skipTriggerChange) {
        this.trigger('change');
      }
    }
  },

  enableTriggerChangeOnInput: function() {
    // `propertychange` and `keyup` needed for IE9
    this.getInput().on('input propertychange keyup', this.triggerChange.bind(this));
  }
});
