ready: function() {
	var field = this;
	this.$(".captcha_container:eq(0)").find(".reload").click(function(){
		field.refresh();
	});
	this.getForm().bind("error", function(){
		field.refresh();
	});
	
},

refresh: function(){
	var field = this;
	cm.rpc(this._class + ".createNumber", [], {
		success: function(id) {
			var $container = field.$(".captcha_container:eq(0)");
			var $img = $container.find("img");
			$img.attr("src", $img.attr("src").replace(/\?[^\?]+$/, '?id=' + id));
			$container.find("input[name=\'captcha[id]\']").val(id);
			$container.find("input[name=\'captcha[value]\']").val("").focus();
		}
	});
}
