/**
 * @class CM_FormField_Textarea
 * @extends CM_FormField_Text
 */
var CM_FormField_Textarea = CM_FormField_Text.extend({
  _class: 'CM_FormField_Textarea',

  ready: function() {
    this._initPlaceholder();
    this._initPlaintextonly();
    this._initChangeEmitter();
  },

  /**
   * @returns {jQuery}
   */
  getInput: function() {
    return this.$('[contenteditable]');
  },

  getValue: function() {
    return this.getInput().html();
  },

  setValue: function(value) {
    return this.getInput().html(value);
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
    if (Modernizr['contenteditable-plaintext']) {
      this.getInput().attr('contenteditable', 'plaintext-only')
    } else {
      this.getInput().on('paste', function(e) {
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
  },

  _initChangeEmitter: function() {
    this.getInput().on('focus', function() {
      var $this = $(this);
      $this.data('before', $this.html());
    }).on('blur keyup paste input', function() {
      var $this = $(this);
      if ($this.data('before') !== $this.html()) {
        $this.data('before', $this.html());
        $this.trigger('change');
      }
    });
  }


});
