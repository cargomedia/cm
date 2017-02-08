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
    },
    'keydown [contenteditable]': function(event) {
      if (this.getOptions().lengthMax > 0) {
        var isRemovalKey = _.contains([cm.keyCode.DELETE, cm.keyCode.BACKSPACE, cm.keyCode.INSERT], event.which);
        var isControl = event.altKey || event.ctrlKey || event.metaKey || event.shiftKey;
        var isExceeded = this.getValue().length > this.getOptions().lengthMax;
        if (!isRemovalKey && !isControl && isExceeded) {
          event.preventDefault();
        }
        if (isExceeded) {
          this.error(cm.language.get('Too long'));
        } else {
          this.error(null);
        }
      }
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

      //scroll to cursor if it goes out of scope
      var selection = window.getSelection();
      var range = selection && selection.getRangeAt(0);
      var textCoords = range && range.getClientRects()[0];
      var textareaCoords = this.getBoundingClientRect();
      if (textCoords && textCoords.bottom > textareaCoords.bottom || textCoords.bottom < textareaCoords.top) {
        $(this).scrollTop($(this).scrollTop() + (textCoords.bottom - textareaCoords.top));
      }
    });
  }
});
