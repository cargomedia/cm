/**
 * @class CM_FormField_Captcha
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Captcha = CM_FormField_Abstract.extend({
  _class: 'CM_FormField_Captcha',

  ready: function() {
    var field = this;
    this.$(".captcha:eq(0)").find(".refreshCaptcha").click(function() {
      field.refresh();
    });
    this.getForm().bind("error", function() {
      field.refresh();
    });
  },

  refresh: function() {
    this.ajax('createNumber', {})
      .then(function(id) {
        var $container = this.$(".captcha:eq(0)");
        var $img = $container.find("img");
        $img.attr("src", $img.attr("src").replace(/\?[^\?]+$/, '?id=' + id));
        $container.find("input[name*=id]").val(id);
        $container.find("input[name*=value]").val("").focus();
      });
  },

  getValue: function() {
    return {
      id: this.$("input[name*=id]").val(),
      value: this.$("input[name*=value]").val()
    }
  }
});
