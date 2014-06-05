/**
 * @class CM_Component_EmailPreview
 * @extends CM_Component_Abstract
 */
var CM_Component_EmailPreview = CM_Component_Abstract.extend({
  _class: 'CM_Component_EmailPreview',

  html: null,

  ready: function() {
    var $iframe = this.$('.htmlPreview');
    $iframe.attr('src', 'data:text/html;charset=utf-8,' + encodeURI(this.html));
  }
});
