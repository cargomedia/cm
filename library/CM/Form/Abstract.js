/**
 * @class CM_Form_Abstract
 * @extends CM_View_Abstract
 */
var CM_Form_Abstract = CM_View_Abstract.extend({
  _class: 'CM_Form_Abstract',

  /** @type String **/
  autosave: null,

  /** @type Object **/
  _fields: {},

  /** @type Object **/
  _actions: {},

  /** @type Array **/
  _autosaveFields: [],

  /** @type PromiseThrottled */
  _autosaveSubmitThrottled: promiseThrottler(function() {
    return this
      .try(function() {
        return this._submitOnly(this.autosave, false);
      })
      .then(function() {
        this._autosaveFields.forEach(function(field) {
          field.success();
        });
      })
      .finally(function() {
        this._autosaveFields = [];
      })
      .catch(CM_Exception_FormFieldValidation, function(error) {
        this._displayValidationError(error);
      });
  }, {cancelLeading: true}),

  initialize: function() {
    CM_View_Abstract.prototype.initialize.call(this);

    this._fields = {};
    this._actions = {};
  },

  events: {
    'reset': function() {
      _.each(this._fields, function(field) {
        field.reset();
      });
      this.resetErrors();
    }
  },

  ready: function() {
  },

  _ready: function() {
    var handler = this;

    _.each(this._actions, function(action, name) {
      var $btn = $('#' + this.getAutoId() + '-' + name + '-button');
      var event = $btn.data('event');
      if (!event) {
        event = 'click';
      }
      $btn.on(event, {action: name}, function(event) {
        event.preventDefault();
        event.stopPropagation();
        return handler.submit(event.data.action);
      });
    }, this);

    if (this.autosave) {
      this.on('change', function(field) {
        if (field) {
          handler._autosaveFields.push(field);
          handler._autosaveSubmitThrottled();
        }
      });
    } else {
      this.$el.on('submit', function() {
        handler.$el.find('input[type="submit"], button[type="submit"]').first().click();
        return false;
      });
    }

    this.$el.on('reset', function() {
      _.defer(function() {
        handler.trigger('reset');
      });
    });

    CM_View_Abstract.prototype._ready.call(this);
  },

  /**
   * @param {CM_FormField_Abstract} field
   */
  registerField: function(field) {
    this._fields[field.getName()] = field;

    field.on('change', function() {
      this.trigger('change', field);
    }, this);
  },

  /**
   * @param {String} name
   * @param {Object} presentation
   */
  registerAction: function(name, presentation) {
    this._actions[name] = presentation;
  },

  /**
   * @return CM_FormField_Abstract
   */
  getField: function(name) {
    if (!this._fields[name]) {
      throw new CM_Exception(this.getClass() + ' cannot find form field `' + name + '`');
    }
    return this._fields[name];
  },

  /**
   * @return Boolean
   */
  hasField: function(name) {
    return !!this._fields[name];
  },

  /**
   * @return jQuery
   */
  $: function(selector) {
    if (!selector) {
      return this.$el;
    }
    selector = selector.replace('#', '#' + this.getAutoId() + '-');
    return $(selector, this.el);
  },

  /**
   * @returns {String[]}
   */
  getFieldNames: function() {
    return _.keys(this._fields);
  },

  /**
   * @returns {CM_FormField_Abstract[]}
   */
  getFields: function() {
    return _.values(this._fields);
  },

  /**
   * @returns {{}}
   */
  getData: function() {
    var data = {};
    _.each(this._fields, function(field) {
      if (field.getEnabled()) {
        data[field.getName()] = field.getValue();
      }
    });
    return data;
  },

  /**
   * @param {String} actionName
   * @returns {{}}
   */
  getActionData: function(actionName) {
    var action = this._getAction(actionName);
    var data = {};

    _.each(action.fields, function(isRequired, fieldName) {
      if (this.hasField(fieldName)) {
        var field = this.getField(fieldName);
        if (field.getEnabled()) {
          data[field.getName()] = field.getValue();
        }
      }
    }, this);

    return data;
  },

  /**
   * @param {String} actionName
   * @return Promise
   */
  submit: function(actionName) {
    return this
      .try(function() {
        return this._submitOnly(actionName, true);
      })
      .catch(CM_Exception_FormFieldValidation, function(error) {
        this._displayValidationError(error);
      });
  },

  /**
   * @param {String} actionName
   * @param {Boolean} disableUI
   * @return Promise
   */
  _submitOnly: function(actionName, disableUI) {
    var action = this._getAction(actionName);
    var data = this.getActionData(action.name);
    var errorListRequired = this._getErrorListRequired(action.name, data);

    this.resetErrors();
    if (_.size(errorListRequired)) {
      var error = new CM_Exception_FormFieldValidation();
      error.setErrorList(errorListRequired);
      return Promise.reject(error);
    }

    return this
      .try(function() {
        if (disableUI) {
          this.disable();
        }
        this.trigger('submit', [data]);
        return cm.ajax('form', {viewInfoList: this.getViewInfoList(), actionName: action.name, data: data})
      })
      .then(function(response) {
        if (response.errors) {
          var error = new CM_Exception_FormFieldValidation();
          error.setErrorList(response.errors);
          this.trigger('error error.' + actionName, error);
          throw error;
        }

        if (response.exec) {
          this.evaluation = new Function(response.exec);
          this.evaluation();
        }

        if (response.messages) {
          for (var i = 0, msg; msg = response.messages[i]; i++) {
            this.message(msg);
          }
        }

        this.trigger('success success.' + action.name, response.data);
        return response.data;
      })
      .finally(function() {
        if (disableUI) {
          this.enable();
        }
      });
  },

  /**
   * @param {CM_Exception_FormFieldValidation} validationError
   */
  _displayValidationError: function(validationError) {
    var errorList = validationError.getErrorList();
    for (var i = errorList.length - 1, error; error = errorList[i]; i--) {
      if (_.isArray(error)) {
        this.getField(error[1]).error(error[0]);
      } else {
        this.error(error);
      }
    }
  },

  reset: function() {
    this.el.reset();
  },

  disable: function() {
    this.$().disable();
  },

  enable: function() {
    this.$().enable();
  },

  /**
   * @param {String} message
   */
  error: function(message) {
    cm.window.hint(message);
  },

  /**
   * @param {String} message
   */
  message: function(message) {
    cm.window.hint(message);
  },

  resetErrors: function() {
    _.each(this._fields, function(field) {
      field.error(null);
    });
  },

  /**
   * @param {String} actionName
   * @returns {Object}
   */
  _getAction: function(actionName) {
    var action = this._actions[actionName];
    if (!action) {
      throw new CM_Exception('Form `' + this.getClass() + '` has no action `' + actionName + '`.');
    }
    action.name = actionName;
    return action;
  },

  /**
   * @param {String} actionName
   * @param {Object} data
   * @returns {Array[]}
   */
  _getErrorListRequired: function(actionName, data) {
    var action = this._getAction(actionName);
    var errorList = [];

    _.each(action.fields, function(isRequired, fieldName) {
      if (isRequired) {
        var field = this.getField(fieldName);
        if (field.isEmpty(data[fieldName])) {
          var label = this._getFieldLabel(field);
          if (label) {
            errorList.push([cm.language.get('{$label} is required.', {label: label}), fieldName]);
          } else {
            errorList.push([cm.language.get('Required'), fieldName]);
          }
        }
      }
    }, this);

    return errorList;
  },

  /**
   * @param {CM_FormField_Abstract} field
   * @returns {String|null}
   * @private
   */
  _getFieldLabel: function(field) {
    var $labels = this.$('label[for="' + field.getAutoId() + '-input"]');
    if ($labels.length) {
      return $labels.first().text();
    }
    if (field.getInput().attr('placeholder')) {
      return field.getInput().attr('placeholder');
    }
    return null;
  }
});
