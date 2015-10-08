/**
 * @class CM_Form_Example
 * @extends CM_Form_Abstract
 */
var CM_Form_Example = CM_Form_Abstract.extend({
  _class: 'CM_Form_Example',

  events: {
    'click .showClientData': function() {
      console.log(this.getData());
    },
    'click .showServerData': function() {
      this.ajax('validate', {data: this.getData()}).then(function(result) {
        console.log(result);
      });
    }
  }
});
