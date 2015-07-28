/**
 * @class CM_FormField_Password
 * @extends CM_FormField_Text
 */
var CM_FormField_Password = CM_FormField_Text.extend({
    _class: 'CM_FormField_Password',


    events: {
        'focus input[type=password]': function (e) {
            this.togglePasswordMask(e);
        },
        'click .togglePasswordMask': function (e) {
            this.togglePasswordMask(e);
        }
    },

    togglePasswordMask: function (e) {
        var $input = this.$('input');
        var $buttonText = this.$('.mode-text');
        var $buttonPassword = this.$('.mode-password');
        var showText = ($input.attr('type') === 'password');

        if (e.type === 'focusin' && $input.val().length !== 0) {
            return;
        }

        $input.attr('type', showText ? 'text' : 'password');
        $buttonText.toggle(showText);
        $buttonPassword.toggle(!showText);
    }
});
