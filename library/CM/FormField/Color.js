var CM_FormField_Abstract = require('CM/FormField/Abstract');

/**
 * @class CM_FormField_Color
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Color = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Color',

  events: {
    'change input': function() {
      this.trigger('change');
    }
  }
});


module.exports = CM_FormField_Color;