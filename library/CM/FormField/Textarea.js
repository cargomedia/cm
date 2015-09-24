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

    }
  }

});
