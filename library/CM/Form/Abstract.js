/**
 * @class CM_Form_Abstract
 * @extends CM_View_Abstract
 */
var CM_Form_Abstract = CM_View_Abstract.extend({
  _class: 'CM_Form_Abstract',

  /** @type Object **/
  _fields: {},

  /** @type Object **/
  _actions: {},

  /** @type Boolean **/
  _stopErrorPropagation: false,

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
        return handler.submit(event.data.action).catch(CM_Exception_FormFieldValidation, function(error) {
          // this error type is already handled and displayed in `submit`
        });
      });
    }, this);

    this.$el.on('submit', function() {
      handler.$el.find('input[type="submit"], button[type="submit"]').first().click();
      return false;
    });

    CM_View_Abstract.prototype._ready.call(this);
  },

  /**
   * @param {CM_FormField_Abstract} field
   */
  registerField: function(field) {
    this._fields[field.getName()] = field;

    field.on('change', function() {
      this.trigger('change');
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
   * @param {String} actionName
   * @returns {{}}
   */
  getData: function(actionName) {
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
   * @param {String} [actionName]
   * @param {Object} [options]
   * @return Promise
   */
  submit: function(actionName, options) {
    options = _.defaults(options || {}, {
      handleErrors: true,
      disableUI: true
    });
    var action = this._getAction(actionName);
    var data = this.getData(action.name);
    var errorList = this._getErrorList(action.name, data);

    if (options.handleErrors) {
      _.each(this._fields, function(field, fieldName) {
        if (errorList[fieldName]) {
          field.error(errorList[fieldName]);
        } else {
          field.error(null);
        }
      }, this);
    }

    if (_.size(errorList)) {
      return Promise.reject(new CM_Exception_FormFieldValidation(errorList));
    } else {
      if (options.disableUI) {
        this.disable();
      }
      this.trigger('submit', [data]);

      var handler = this;
      return cm.ajax('form', {viewInfoList: this.getViewInfoList(), actionName: action.name, data: data})
        .then(function(response) {
          if (response.errors) {
            if (options.handleErrors) {
              for (var i = response.errors.length - 1, error; error = response.errors[i]; i--) {
                if (_.isArray(error)) {
                  handler.getField(error[1]).error(error[0]);
                } else {
                  handler.error(error);
                }
              }
            }

            throw new CM_Exception_FormFieldValidation(response.errors);
          }

          if (response.exec) {
            handler.evaluation = new Function(response.exec);
            handler.evaluation();
          }

          if (response.messages) {
            for (var i = 0, msg; msg = response.messages[i]; i++) {
              handler.message(msg);
            }
          }

          handler.trigger('success success.' + action.name, response.data);
          return response.data;
        })
        .catch(CM_Exception_FormFieldValidation, function(error) {
          handler._stopErrorPropagation = false;
          handler.trigger('error error.' + action.name, error.message, error.name, error.isPublic);
          if (!handler._stopErrorPropagation) {
            throw error;
          }
        })
        .finally(function() {
          if (options.disableUI) {
            handler.enable();
          }
          handler.trigger('complete');
        });
    }
  },

  stopErrorPropagation: function() {
    this._stopErrorPropagation = true;
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
   * @returns {Object}
   */
  _getErrorList: function(actionName, data) {
    var action = this._getAction(actionName);
    var errorList = {};

    _.each(action.fields, function(isRequired, fieldName) {
      if (isRequired) {
        var field = this.getField(fieldName);
        if (field.isEmpty(data[fieldName])) {
          var label = this._getFieldLabel(field);
          if (label) {
            errorList[fieldName] = cm.language.get('{$label} is required.', {label: label});
          } else {
            errorList[fieldName] = cm.language.get('Required');
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
