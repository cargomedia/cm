var CM_Component_Abstract = require('CM/Component/Abstract');

/**
 * @class CM_Component_EmailPreview
 * @extends CM_Component_Abstract
 */
var CM_Component_EmailPreview = CM_Component_Abstract.extend({
  _class: 'CM_Component_EmailPreview',

  html: null,

  ready: function() {
    var $iframe = this.$('.preview-html');
    $iframe.attr('src', 'data:text/html;charset=utf-8,' + encodeURI(this.html));
  }
});


module.exports = CM_Component_EmailPreview;