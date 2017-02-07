/**
 * @class CM_Janus_StreamChannel
 * @extends CM_Model_Abstract
 */
var CM_Janus_StreamChannel = CM_Model_Abstract.extend({

  _class: 'CM_Janus_StreamChannel',

  /**
   * @returns {CM_Janus_ConnectionDescription}
   */
  getConnectionDescription: function() {
    return this.get('connectionDescription');
  }
});
