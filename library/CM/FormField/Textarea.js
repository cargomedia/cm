/**
 * @class CM_FormField_Textarea
 * @extends CM_FormField_Text
 */
var CM_FormField_Textarea = CM_FormField_Text.extend({
  _class: 'CM_FormField_Textarea',

  ready: function() {
    this._initPlaceholder();
    this._initPlaintextonly();
  },

  getValue: function() {
    return this.$('[contenteditable]').html();
  },

  setValue: function(value) {
    return this.$('[contenteditable]').html(value);
  },

  _initPlaceholder: function() {
    this.$('[contenteditable]').focusout(function() {
      var $this = $(this);
      if (!$this.text().trim().length) {
        $this.empty();
      }
    });
  },

  _initPlaintextonly: function() {
    if (Modernizr['contenteditable-plaintext']) {
      this.$('[contenteditable]').attr('contenteditable', 'plaintext-only')
    } else {
      this.$('[contenteditable]').on('paste', function(e) {
        e.preventDefault();
        var text;
        var clipboardData = (e.originalEvent || e).clipboardData;
        if (_.isUndefined(clipboardData) || clipboardData === null) {
          text = window.clipboardData.getData('text') || '';
          if (text !== '') {
            if (window.getSelection) {
              var newNode = document.createElement('span');
              newNode.innerHTML = text;
              window.getSelection().getRangeAt(0).insertNode(newNode);
            } else {
              document.selection.createRange().pasteHTML(text);
            }
          }
        } else {
          text = clipboardData.getData('text/plain') || '';
          if (text !== '') {
            document.execCommand('insertText', false, text);
          }
        }
      });
    }
  }

});
