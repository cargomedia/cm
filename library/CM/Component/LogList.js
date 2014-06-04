/**
 * @class CM_Component_LogList
 * @extends CM_Component_Abstract
 */
var CM_Component_LogList = CM_Component_Abstract.extend({

  /** @type String */
  _class: 'CM_Component_LogList',

  type: null,

  events: {
    'click .flushLog': 'flushLog'
  },

  flushLog: function() {
    this.ajaxModal('flushLog', {'type': this.type});
  }
});
