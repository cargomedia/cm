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
	sk.rpc(this._class + ".createNumber", [], {
		success: function(id) {
			var $container = field.$(".captcha_container:eq(0)");
			$container.find("img").attr("src", field.getOption("urlImage")+"?id="+id);
			$container.find("input[name=\'captcha[image_id]\']").val(id);
			$container.find("input[name=\'captcha[value]\']").val("").focus();
		}
	});
}
