/**
 * @class CM_Page_Example
 * @extends CM_Page_Abstract
 */
var CM_Page_Example = CM_Page_Abstract.extend({

  /** @type String */
  _class: 'CM_Page_Example',

  _stateParams: ['tab'],

  _changeState: function(state) {
    if (state['tab']) {
      this.findChild('CM_Component_Example').showTab(state['tab']);
    }
  }
});
