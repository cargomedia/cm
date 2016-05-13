/**
 * @class CM_Component_LogList
 * @extends CM_Component_Abstract
 */
var CM_Component_LogList = CM_Component_Abstract.extend({

  /** @type String */
  _class: 'CM_Component_LogList',

  level: null,

  type: null,

  events: {
    'click .flushLog': 'flushLog'
  },

  flushLog: function() {
    this.ajaxModal('flushLog', {level: this.level, type: this.type});
  }
});
