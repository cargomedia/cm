/**
 * @class CM_Form_Example
 * @extends CM_Form_Abstract
 */
var CM_Form_Example = CM_Form_Abstract.extend({
  _class: 'CM_Form_Example',

  ready: function() {
    var form = this;
    this.getFields().forEach(function(field) {
      field.on('ready', function() {
        field.on('change', form.logData.bind(form));
      });
    });

    this.on('success', function(response) {
      cm.window.hint('Form successfully submitted. See browser console for response.');
      console.debug('Response:', response);
    });
  },

  logData: promiseThrottler(function() {
    var table = this._getDataTable();

    var data = this.getData();
    return this.ajax('validate', {data: this.getData()}).then(function(serverData) {
      _.each(serverData, function(serverRow, fieldName) {
        table[fieldName]['value (server)'] = serverRow['value'];
        table[fieldName]['empty (server)'] = serverRow['empty'];
        table[fieldName]['validation (server)'] = serverRow['validationError'];
      });
      console.clear();
      console.table(table);
    });
  }, {cancelLeading: true}),

  /**
   * @returns {Object}
   */
  _getDataTable: function() {
    var table = {};
    this.getFieldNames().forEach(function(fieldName) {
      var field = this.getField(fieldName);
      table[fieldName] = {
        'value (client)': field.getValue(),
        'empty (client)': field.isEmpty(field.getValue())
      };
    }.bind(this));
    return table;
  }
});
