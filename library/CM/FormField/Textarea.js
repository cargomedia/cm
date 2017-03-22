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
    'input [contenteditable]': function() {
      this._cleanEmptyWhiteSpace();
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
        this._checkLengthMax();
      }
    },
    'keyup [contenteditable]': function() {
      console.log('VALUE', JSON.stringify(this.getValue()));
    },
    'paste [contenteditable]': function(event) {
      event.stopPropagation();
      event.preventDefault();
      var clipboardData = (event.originalEvent || event).clipboardData || window.clipboardData;
      var text = clipboardData.getData('text/plain');
      console.log('pasting...', JSON.stringify(text));
      this._pasteTextAtCursor(text);
      this._checkLengthMax();
      this._scrollToCursor();
    }
  },

  ready: function() {
    if (Modernizr.plaintextonly) {
      this.getInput().attr('contenteditable', 'plaintext-only');
    }
  },

  getInput: function() {
    return this.$('[contenteditable]');
  },

  getValue: function() {
    var $input = this.getInput();
    var value;
    if ('plaintext-only' === $input.attr('contenteditable')) {
      // Chrome, Safari, Edge
      value = $input.text();
    } else {
      // Firefox (see https://bugzilla.mozilla.org/show_bug.cgi?id=1291467)
      $input = $input.clone();
      $input.find('br').replaceWith('\n');
      value = $input.text();
    }

    // Browsers add an additional newline to the end of "contenteditable" content
    value = value.replace(/\r?\n$/, '');

    return value;
  },

  setValue: function(value) {
    this.getInput().html(value);
  },

  getEnabled: function() {
    return true;
  },

  reset: function() {
    this.setValue('');
  },

  _cleanEmptyWhiteSpace: function() {
    if ('' === this.getValue()) {
      // Getting rid of the extra newline added by browsers to the end of the "contenteditable"
      this.getInput().empty();
    }
  },

  _checkLengthMax: function() {
    if (this.getOptions().lengthMax && this.getValue().length > this.getOptions().lengthMax) {
      this.error(cm.language.get('Too long'));
    } else {
      this.error(null);
    }
  },

  _scrollToCursor: function() {
    var $input = this.getInput();
    var selection = window.getSelection();
    var range = selection && selection.getRangeAt(0);
    var selectionCoords = range && range.getClientRects()[0];
    selectionCoords = selectionCoords || {bottom: $input.height()};
    var textareaCoords = $input[0].getBoundingClientRect();
    if (selectionCoords && selectionCoords.bottom > textareaCoords.bottom || selectionCoords.bottom < textareaCoords.top) {
      $input.scrollTop($input.scrollTop() + (selectionCoords.bottom - textareaCoords.top));
    }
  },

  /**
   * @param {String} text
   */
  _pasteTextAtCursor: function(text) {
    if (!_.isEmpty(text)) {
      document.execCommand('insertText', false, text);
    }
  }
});
