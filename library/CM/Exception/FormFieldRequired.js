(function(global) {

  /**
   * @class CM_Exception_FormFieldRequired
   * @extends CM_Exception_FormFieldValidation
   */
  function CM_Exception_FormFieldRequired() {
    global.CM_Exception_FormFieldValidation.apply(this, arguments);
    this.name = 'CM_Exception_FormFieldRequired';
  }

  CM_Exception_FormFieldRequired.prototype = Object.create(global.CM_Exception_FormFieldValidation.prototype);
  CM_Exception_FormFieldRequired.prototype.constructor = CM_Exception_FormFieldRequired;

  global.CM_Exception_FormFieldRequired = CM_Exception_FormFieldRequired;
})(window);