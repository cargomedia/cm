/**
 * @class CM_FormField_FileReader
 * @extends CM_FormField_Abstract
 */
var CM_FormField_FileReader = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_FileReader',

  /** @type {Boolean} */
  instantUpload: null,

  /** @type {Boolean} */
  skipPreviews: null,

  /** @type {Array} */
  files: [],

  events: {
    'click .removeFile': function(e) {
      var $item = $(e.currentTarget).closest('.preview');
      this.files.splice($item.index(), 1);
      $item.remove();
    }
  },

  ready: function() {
    var self = this;
    var field = this;
    var $input = this.getInput();

    if (field.getOption("cardinality") == 1) {
      $input.removeAttr('multiple');
    }

    var options = {
      on: {
        load: function(e, file) {
          var fileData = {
            type: file.type,
            name: file.name,
            data: e.target.result,
            isImage: file.type.match(/image/)
          };

          self.files.push(fileData);

          if (!self.skipPreviews) {
            self._renderPreview(fileData);
          }

          // self.trigger('change');
        },
        error: function(e, file) {
          self.error(cm.language.get('Unable to read {$file}', {'file': file.name}));
        }
      }
    };

    FileReaderJS.setupInput($input.get(0), options);
    FileReaderJS.setupDrop(field.el, options);
    FileReaderJS.setupClipboard(document.body, options);
  },

  getInput: function() {
    return this.$('input[type="file"]');
  },

  getValue: function() {
    return _.compact(this.files);
  },

  setValue: function(value) {
    throw new CM_Exception('Not implemented');
  },

  reset: function() {
    this.files = [];
    this.$('.previews').empty();
  },

  /**
   * @param {Object} renderParams
   * @private
   */
  _renderPreview: function(renderParams) {
    var $preview = this.renderTemplate('tpl-preview', renderParams);
    this.$('.previews').append($preview);
  }
});
