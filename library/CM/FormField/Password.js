/**
 * @class CM_FormField_Password
 * @extends CM_FormField_Text
 */
var CM_FormField_Password = CM_FormField_Text.extend({
  _class: 'CM_FormField_Password',

  _visibilityDesired: false,

  _valueLast: '',

  events: {
    'click .togglePasswordVisibility': function() {
      this.togglePasswordVisibility();
      this.getInput().focus();
    }
  },

  ready: function() {
    var self = this;
    var visibilityDesired;
    this.getForm()
      .on('submit', function() {
        visibilityDesired = self._visibilityDesired;
        self.togglePasswordVisibility(false);
      })
      .on('error', function() {
        self.togglePasswordVisibility(visibilityDesired);
      });
  },

  triggerChange: function() {
    var valueCurrent = this.getInput().val();
    //it means we that 'paste' event has occurred.
    if (valueCurrent.length - this._valueLast.length > 1) {
      this.togglePasswordVisibility(false);
    }
    CM_FormField_Text.prototype.triggerChange.apply(this, arguments);
  },

  /**
   * @param {Boolean} [state]
   */
  togglePasswordVisibility: function(state) {
    if (_.isUndefined(state)) {
      state = !this._visibilityDesired;
    }
    this._visibilityDesired = state;
    this.$('.mode-text').toggle(state);
    this.$('.mode-password').toggle(!state);
    this._applyDesiredPasswordVisibility();
  },

  _applyDesiredPasswordVisibility: function() {
    this._setInputTypeByState(this._visibilityDesired);
  },

  /**
   * @param {Boolean} state
   */
  _setInputTypeByState: function(state) {
    this.getInput().attr('type', state ? 'text' : 'password');
  }
});
