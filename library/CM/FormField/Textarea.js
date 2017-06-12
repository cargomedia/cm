/**
 * @class CM_FormField_Textarea
 * @extends CM_FormField_Text
 */
var CM_FormField_Textarea = CM_FormField_Text.extend({
  _class: 'CM_FormField_Textarea',

  events: {
    'blur [contenteditable]': function() {
      this.trigger('blur');
      this.triggerChange();
    },
    'focus [contenteditable]': function() {
      this.trigger('focus');
    },
    'input [contenteditable]': function() {
      this.triggerChange();
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
      var $input = this.getInput();
      if ('plaintext-only' !== $input.attr('contenteditable')) {
        event.stopPropagation();
        event.preventDefault();
        var clipboardData = (event.originalEvent || event).clipboardData || window.clipboardData;
        var text = clipboardData.getData('text/plain');
        var html = this._convertTextToHtml(text);
        document.execCommand('insertHTML', false, html);
        this._checkLengthMax();
      }
    }
  },

  ready: function() {
    CM_FormField_SuggestOne.prototype.ready.call(this);

    this.on('change', function() {
      this._cleanEmptyWhiteSpace();
    });

    if (Modernizr.plaintextonly) {
      /**
       * Use "plaintext-only" attribute where available.
       * It's currently not available on Firefox (see https://bugzilla.mozilla.org/show_bug.cgi?id=1291467).
       * For iOS this is needed to prevent "styling" options to be displayed in the context menu.
       */
      this.getInput().attr('contenteditable', 'plaintext-only');
    }
  },

  getInput: function() {
    return this.$('[contenteditable]');
  },

  getValue: function() {
    var input = this.getInput().get(0);
    var value = input.innerText;

    // Browsers add an additional newline to the end of "contenteditable" content
    value = value.replace(/\r?\n$/, '');

    return value;
  },

  setValue: function(value) {
    var html = this._convertTextToHtml(value);
    this.getInput().html(html);
  },

  getEnabled: function() {
    return true;
  },

  reset: function() {
    this.setValue('');
  },

  disableTriggerChangeOnInput: function() {
    this.delegateEvents(
      _(this.events).omit('input [contenteditable]')
    );
  },

  /**
   * @param {String} text
   * @returns {string}
   * @private
   */
  _convertTextToHtml: function(text) {
    var html = _.escape(text);
    html = html.replace(/\r?\n/g, '<br>');
    return '<div>' + html + '</div>';
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
  }
});
