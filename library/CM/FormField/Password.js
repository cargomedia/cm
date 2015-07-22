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
    var $inputPassword = this.$('input[type=password]');
    var $inputText = this.$('input[type=text]');

    if ($inputText.is(':visible')) {
      $inputPassword.val($inputText.val());
      this.$('.mode-visible').hide();
      this.$('.mode-hidden').show();

    } else {
      $inputText.val($inputPassword.val());
      this.$('.mode-visible').show();
      this.$('.mode-hidden').hide();
    }
  }
});
