/**
 * @class CM_FormField_Text
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Text = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Text',

  /** @type Boolean */
  _skipTriggerChange: false,

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
    if (this._skipTriggerChange) {
      return;
    }
    this.trigger('change');
  },

  enableTriggerChangeOnInput: function() {
    var self = this;
    var $input = this.getInput();
    var valueLast = $input.val();
    var callback = function() {
      var value = this.value;
      if (value != valueLast) {
        valueLast = value;
        this.triggerChange();
      }
    };
    // `propertychange` and `keyup` needed for IE9
    $input.on('input propertychange keyup', callback);
  }
});
