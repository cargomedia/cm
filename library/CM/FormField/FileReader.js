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

  /** @type {Number} */
  _minWidth: null,

  /** @type {Number} */
  _minHeight: null,

  /** @type {Number} */
  _maxWidth: null,

  /** @type {Number} */
  _maxHeight: null,

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

          if (cardinality > field.files.length) {
            field._fileValidation(fileData)
              .then(function(fileData) {

                  field.files.push(fileData);

                  if (!field.skipPreviews) {
                    field._renderPreview(fileData);
                  }

                  field.trigger('change');
                });
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
    this.files = value;
  },

  reset: function() {
    this.files = [];
    this.$('.previews').empty();
  },

  _fileValidation: function(fileData) {
    var self = this;
    return new Promise(function(resolve, reject) {
      var allowedExtensions = self.getOption('allowedExtensions');

      if (!_.contains(allowedExtensions, fileData.extension)) {
        reject(self.error(cm.language.get('File type not supported. Allowed file extensions: {$allowedExtensions}.', {'allowedExtensions': allowedExtensions.join(', ')})));
        return;
      }

      if (fileData.isImage) {
        // Resize image if maxWidth/maxHeight exceeded. For huge images this may fail due memory limitations on some platforms (iOS).
        self._validateImageData(fileData.data, self._minWidth, self._minHeight, self._maxWidth, self._maxHeight).then(
          function(result) {
            fileData.data = result;
            resolve(fileData);
          }
        )
      } else {
        resolve(fileData);
      }
    });
  },

  /**
   * @param {Object} renderParams
   * @private
   */
  _renderPreview: function(renderParams) {
    var $preview = this.renderTemplate('tpl-preview', renderParams);
    this.$('.previews').append($preview);
  },

  _validateImageData: function(imgData, minWidth, minHeight, maxWidth, maxHeight) {
    var self = this;

    return new Promise(function(resolve, reject) {
      var img = new Image();
      img.onload = function() {
        if (img.width < minWidth) {
          reject(self.error(cm.language.get('Image is too small (min width {$minWidth}px).', {'minWidth': minWidth})));
        }
        if (img.height < minHeight) {
          reject(self.error(cm.language.get('Image is too small (min height {$minHeight}px).', {'minHeight': minHeight})));
        }
        if (img.width > maxWidth || img.height > maxHeight) {
          var canvas = document.createElement('canvas'),
            ctx = canvas.getContext('2d'),
            imageWidth = img.width,
            imageHeight = img.height,
            scale = Math.min((maxWidth / imageWidth), (maxHeight / imageHeight)),
            imageWidthScaled = imageWidth * scale,
            imageHeightScaled = imageHeight * scale;

          canvas.width = imageWidthScaled;
          canvas.height = imageHeightScaled;

          ctx.drawImage(img, 0, 0, imageWidthScaled, imageHeightScaled);

          resolve(canvas.toDataURL());
        } else {
          resolve(imgData)
        }
      };

      img.src = imgData;
    });
  }
});
