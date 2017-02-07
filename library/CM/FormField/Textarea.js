/**
 * @class CM_FormField_Textarea
 * @extends CM_FormField_Text
 */
var CM_FormField_Textarea = CM_FormField_Text.extend({
  _class: 'CM_FormField_Textarea',

  events: {
    'blur [contenteditable]': function() {
      this.trigger('blur');
    },
    'focus [contenteditable]': function() {
      this.trigger('focus');
    },
    'change [contenteditable]': function() {
      this.triggerChange();
    }
  },

  ready: function() {
    this._initPlaceholder();
    this._initPlaintextonly();
    this.enableTriggerChangeOnInput();
  },

  getInput: function() {
    return this.$('[contenteditable]');
  },

  getValue: function() {
    return this.getInput().html();
  },

  setValue: function(value) {
    this.getInput().html(value);
  },

  getEnabled: function() {
    return true;
  },

  _initPlaceholder: function() {
    this.getInput().focusout(function() {
      var $this = $(this);
      if (!$this.text().trim().length) {
        $this.empty();
      }
    });
  },

  _initPlaintextonly: function() {
    this.getInput().on('paste', function(e) {
      e.stopPropagation();
      e.preventDefault();
      var clipboardData = (e.originalEvent || e).clipboardData || window.clipboardData;
      var text = clipboardData.getData('text/plain');
      cm.dom.pasteTextAtCursor(text);
    });
  }
});
