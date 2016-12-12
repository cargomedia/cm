/**
 * @class CM_Exception_FormFieldValidation
 * @extends CM_Exception
 */
window.CM_Exception_FormFieldValidation = CM_Exception.extend('CM_Exception_FormFieldValidation');

/**
 * @param {Object[]} errorList
 */
CM_Exception_FormFieldValidation.prototype.setErrorList = function(errorList) {
  this._errorList = errorList;
  this.message = errorList.join();
};

/**
 * @returns {Object[]|null}
 */
CM_Exception_FormFieldValidation.prototype.getErrorList = function() {
  return this._errorList || null;
};
