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

    if ($input.attr('type') === 'text') {
      this.$('input').attr('type', 'password');
      this.$('.mode-visible').hide();
      this.$('.mode-hidden').show();
    } else {
      this.$('input').attr('type', 'text');
      this.$('.mode-hidden').hide();
      this.$('.mode-visible').show();
    }
  }
});
