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
	this.ajax('createNumber', {}, {
		success: function(id) {
			var $container = this.$(".captcha_container:eq(0)");
			var $img = $container.find("img");
			$img.attr("src", $img.attr("src").replace(/\?[^\?]+$/, '?id=' + id));
			$container.find("input[name=\'captcha[id]\']").val(id);
			$container.find("input[name=\'captcha[value]\']").val("").focus();
		}
	});
}
