/**
 * @class CM_FormField_FileReader
 * @extends CM_FormField_Abstract
 */
var CM_FormField_FileReader = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_FileReader',

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
    var field = this;
    var $input = this.getInput();
    var cardinality = field.getOption('cardinality');
    var allowedExtensions = field.getOption('allowedExtensions');

    if (cardinality == 1) {
      $input.removeAttr('multiple');
    }

    var options = {
      on: {
        load: function(e, file) {
          field.error(null);

          var fileData = {
            type: file.type,
            extension: file.extra.extension,
            name: file.name,
            data: e.target.result,
            isImage: /image/.test(file.type)
          };

          if (!_.contains(allowedExtensions, fileData.extension)) {
            field.error(cm.language.get('File type not supported. Allowed file extensions: {$allowedExtensions}.', {'allowedExtensions': allowedExtensions.join(', ')}));
            return;
          }

          if (cardinality > field.files.length) {
            field.files.push(fileData);

            if (!field.skipPreviews) {
              field._renderPreview(fileData);
            }

            field.trigger('change');
          } else {
            field.error(cm.language.get('You can only select {$cardinality} items.', {cardinality: cardinality}));
          }
        },
        error: function(e, file) {
          field.error(cm.language.get('Unable to read {$file}', {'file': file.name}));
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
    return window.btoa(JSON.stringify(_.compact((this.files))));
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
