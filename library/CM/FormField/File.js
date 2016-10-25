/**
 * @class CM_FormField_File
 * @extends CM_FormField_Abstract
 */
var CM_FormField_File = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_File',

  events: {
    'click .deleteFile': function(e) {
      $(e.currentTarget).closest('.preview').remove();
    }
  },

  ready: function() {
    var field = this;
    var $input = this.getInput();
    var allowedExtensions = field.getOption("allowedExtensions");
    var allowedExtensionsRegexp = _.isEmpty(allowedExtensions) ? null : new RegExp('\.(' + allowedExtensions.join('|') + ')$', 'i');
    var inProgressCount = 0;

    if (!Modernizr.fileinput) {
      $input.prop('disabled', true);
      field.$('.uploadButton').addClass('disabled');
      field.$('.notSupported').show();
    }

    // remove attr multiple on iPhone, iPod, iPad to allow upload photos via camera
    if (navigator.userAgent.match(/iP(ad|hone|od)/i)) {
      $input.removeAttr('multiple');
    }

    $input.fileupload({
      dataType: 'json',
      url: cm.getUrl('/upload', {'field': field.getClass()}, true),
      dropZone: this.$el,
      acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
      formData: function(form) {
        return $input;
      },
      send: function(e, data) {
        inProgressCount++;
        field.error(null);
        _.each(data.files, function(file) {
          if (allowedExtensionsRegexp && !allowedExtensionsRegexp.test(file.name)) {
            field.error(cm.language.get('{$file} has an invalid extension. Only {$extensions} are allowed.', {
              file: file.name,
              extensions: allowedExtensions.join(', ')
            }));
            file.error = true;
          }
        });
        data.files = _.reject(data.files, function(file) {
          return file.error;
        });
        if (_.isEmpty(data.files)) {
          data.skipFailMessage = true;
          return false;
        }
        data.$preview = $('<li class="preview"></li>');
        field.$('.previews').append(data.$preview);

        field.$('.uploadButton').attr('data-progress', '');

      },
      done: function(e, data) {
        inProgressCount--;
        if (data.result.success) {
          data.$preview.html(data.result.success.preview + '<input type="hidden" name="' + field.getName() + '[]" value="' + data.result.success.id + '"/>');
        } else if (data.result.error) {
          data.$preview.remove();
          field.error(data.result.error.msg);
        }
        if (inProgressCount === 0) {
          field.trigger("uploadComplete", data.files);
        }
      },
      fail: function(e, data) {
        inProgressCount--;
        if (data.$preview) {
          data.$preview.remove();
        }
        if (!data.skipFailMessage) {
          field.error('Upload error');
        }
      },
      always: function() {
        if (inProgressCount === 0) {
          field.$('.uploadButton').removeAttr('data-progress');
        }
      }
    });

    this.bindJquery($(document), 'dragenter', function() {
      field.$el.addClass('dragover');
    });
    this.bindJquery($(document), 'drop', function() {
      field.$el.removeClass('dragover');
    });
    this.bindJquery($(document), 'drop dragover', function(e) {
      e.preventDefault();
    });
  },

  getInput: function() {
    return this.$('input[type="file"]');
  },

  getValue: function() {
    var value = this.$('input[name="' + this.options.params.name + '[]"]').map(function() {
      return $(this).val();
    }).get();
    value = _.compact(value);
    return value;
  },

  setValue: function(value) {
    throw new CM_Exception('Not implemented');
  },

  reset: function() {
    this.$('.previews').empty();
  }
});
