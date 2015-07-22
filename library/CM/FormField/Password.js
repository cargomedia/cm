/**
 * @class CM_FormField_Password
 * @extends CM_FormField_Text
 */
var CM_FormField_Password = CM_FormField_Text.extend({
  _class: 'CM_FormField_Password',

  events: {
    'click .togglePasswordMask': 'togglePasswordMask'
  },

  togglePasswordMask: function() {
    var $input = this.$('input');
    var $buttonText = this.$('.mode-text');
    var $buttonPassword = this.$('.mode-password');

    var showText = ($input.attr('type') === 'password');
    $input.attr('type', showText ? 'text' : 'password');
    $buttonText.toggle(showText);
    $buttonPassword.toggle(!showText);
  }
});
