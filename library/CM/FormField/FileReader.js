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
      this._handleUpload();
    },
    'click .uploadFiles': function() {
      this._handleUpload(true)
    }
  },

  ready: function() {
    var field = this;
    var $input = this.getInput();
    var cardinality = field.getOption('cardinality');

    if (cardinality == 1) {
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

          if (cardinality > field.files.length) {
            field.files.push(fileData);

            if (!field.skipPreviews) {
              field._renderPreview(fileData);
            }

            // field.trigger('change');
          } else {
            field.error(cm.language.get('You can only select {$cardinality} items.', {cardinality: cardinality}));
          }

          field._handleUpload(this.instantUpload);
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
    return _.compact(this.files);
  },

  setValue: function(value) {
    throw new CM_Exception('Not implemented');
  },

  reset: function() {
    this.files = [];
    this.$('.previews').empty();
    this._toggleUploadReady(false);
  },

  /**
   * @param {Object} renderParams
   * @private
   */
  _renderPreview: function(renderParams) {
    var $preview = this.renderTemplate('tpl-preview', renderParams);
    this.$('.previews').append($preview);
  },

  /**
   * @param {Boolean|null} upload
   * @private
   */
  _handleUpload: function(upload) {
    if (this.files.length > 0) {
      if (upload) {
        this._processUpload();
      } else {
        this._toggleUploadReady(true);
      }
    } else {
      this.reset()
    }
  },

  /**
   * @param {Boolean} state
   * @private
   */
  _toggleUploadReady: function(state) {
    this.$el.attr('data-upload-ready', state ? '' : null);
  },

  /**
   * @private
   */
  _processUpload: function() {
    this.$el.attr('data-upload-process', '');
    //todo upload
  }
});
