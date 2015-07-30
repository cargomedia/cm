/**
 * @class CM_FormField_Password
 * @extends CM_FormField_Text
 */
var CM_FormField_Password = CM_FormField_Text.extend({
    _class: 'CM_FormField_Password',

    _visibilityDesired: true,

    events: {
        'click .togglePasswordVisibility': function () {
            this.togglePasswordVisibility();
        }
    },

    ready: function () {
        this.enableTriggerChangeOnInput();
        this.on('change', function () {
            this._applyDesiredPasswordVisibility();
        });
        var self = this;
        this.getForm().on('submit', function() {
            self.togglePasswordVisibility(false);
        });
    },

    /**
     * @param {Boolean} [state]
     */
    togglePasswordVisibility: function (state) {
        if ('undefined' === typeof state) {
            state = !this._visibilityDesired;
        }
        this._visibilityDesired = state;
        this.$('.mode-text').toggle(state);
        this.$('.mode-password').toggle(!state);
        this._applyDesiredPasswordVisibility();
    },

    _applyDesiredPasswordVisibility: function () {
        if (0 === this.getValue().length) {
            this._togglePasswordVisibilityInput(false);
        } else {
            this._togglePasswordVisibilityInput(this._visibilityDesired);
        }
    },

    /**
     * @param {Boolean} state
     */
    _togglePasswordVisibilityInput: function (state) {
        this.getInput().attr('type', state ? 'text' : 'password');
    }
});
