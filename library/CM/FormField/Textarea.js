/**selectionCoords
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
    'focusout [contenteditable]': function() {
      this._initPlaceholder();
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
    'paste [contenteditable]': function(event) {
      event.stopPropagation();
      event.preventDefault();
      var clipboardData = (event.originalEvent || event).clipboardData || window.clipboardData;
      var text = clipboardData.getData('text/plain');
      this._pasteTextAtCursor(text);
      this._checkLengthMax();
      this._scrollToCursor();
    }
  },

  ready: function() {
    this._initPlaceholder();
    this.enableTriggerChangeOnInput();
  },

  getInput: function() {
    return this.$('[contenteditable]');
  },

  getValue: function() {
    var $input = this.getInput().clone();
    $input.find('div').replaceWith(function() {
      return '\n' + this.innerHTML;
    });
    $input.find('p').replaceWith(function() {
      return this.innerHTML + '\n';
    });
    $input.find('br').replaceWith('\n');
    return $input.html();
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

  _initPlaceholder: function() {
    var $this = this.getInput();
    if (!$this.text().trim().length) {
      $this.empty();
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
      if (document.execCommand) {
        document.execCommand('insertText', false, text);
      } else if (window.getSelection) {
        var newNode = document.createElement('span');
        newNode.innerHTML = text;
        window.getSelection().getRangeAt(0).insertNode(newNode);
      }
    }
  }
});
