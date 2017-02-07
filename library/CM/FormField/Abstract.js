/**
 * @class CM_FormField_Abstract
 * @extends CM_View_Abstract
 */
var CM_FormField_Abstract = CM_View_Abstract.extend({
  _class: 'CM_FormField_Abstract',

  /** @type Object **/
  fieldOptions: {},

  initialize: function() {
    CM_View_Abstract.prototype.initialize.call(this);

    this.fieldOptions = {};
  },

  ready: function() {
  },

  validate: function() {
    return this.try(function(){
      var value = this.getValue();
      if (this.isEmpty(value)) {
        this.error(null);
        return;
      }
      var self = this;
      return this.ajax('validate', {'userInput': value, 'form': this.getForm().getClass(), 'fieldName': this.getName()})
        .then(function() {
          if (value == self.getValue()) {
            self.error();
          }
        })
        .catch(CM_Exception, function(error) {
          if (error instanceof CM_Exception_FormFieldValidation) {
            self.error(error.message);
          } else if (value == self.getValue()) {
            throw error;
          }
        });
    });
  },

  reset: function() {
  },

  /**
   * @return CM_Form_Abstract
   */
  getForm: function() {
    return this.getParent();
  },

  /**
   * @return jQuery
   */
  $: function(selector) {
    if (!selector) {
      return this.$el;
    }
    return $(selector, this.el);
  },

  /**
   * @return String
   */
  getName: function() {
    return this.options.params.name;
  },

  /**
   * @returns {jQuery}
   */
  getInput: function() {
    var $input = this.$('input:first, select:first');
    if ($input.length === 0) {
      throw new CM_Exception('Can\'t find input for `' + this.getName() + '` field');
    }
    return $input;
  },

  /**
   * @returns {*|String|null}
   */
  getValue: function() {
    return this.getInput().val();
  },

  /**
   * @param {*|String|null} value
   */
  setValue: function(value) {
    this.getInput().val(value);
  },

  /**
   * @returns {Boolean}
   */
  getEnabled: function() {
    return this.getInput().is(':enabled');
  },

  /**
   * @return Object
   */
  getOptions: function() {
    return this.fieldOptions;
  },

  /**
   * @param {String} name
   * @return mixed|null
   */
  getOption: function(name) {
    var options = this.getOptions();
    if (null === options[name] || '' === options[name]) {
      return null;
    }
    return options[name];
  },

  setFocus: function() {
    this.getInput().focus();
  },

  success: function() {
    this.error(null);
    this.$el[0].dataset.formfieldSuccess = true;
  },

  /**
   * @param {String|Null} [message]
   */
  error: function(message) {
    var $container = this.$('.messages');
    var $errorMessage = $container.find('.formField-error');
    var el = this.$el[0];
    delete el.dataset.formfieldError;
    delete el.dataset.formfieldSuccess;

    if (message) {
      if ($container.length) {
        this.$el.triggerReflow();
        el.dataset.formfieldError = true;

        if ($errorMessage.length) {
          $errorMessage.html(message);
        } else {
          $errorMessage = $('<div class="formField-error"></div>').hide().appendTo($container);
          $errorMessage.html(message);
          $errorMessage.slideDown('fast');
        }
      } else {
        throw new CM_Exception('FormField `' + this.getName() + '`: ' + message);
      }
    } else {
      $errorMessage.remove();
    }
  },

  /**
   * @param {Object} value
   * @returns {Boolean}
   */
  isEmpty: function(value) {
    if (_.isNull(value)) {
      return true;
    }
    if (_.isArray(value)) {
      return 0 === value.length;
    }
    if (_.isBoolean(value)) {
      return false;
    }
    return 0 === String(value).trim().length;
  }
});
