/**
 * @class CM_FormField_File
 * @extends CM_FormField_Abstract
 */
var CM_FormField_File = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_File',

	fileUploader: null,
	
	ready: function() {
		var field = this;
		
		this.fileUploader = new qq.FileUploader({
			element: field.$(".file-uploader").get(0),
			action: "/upload/" + cm.options.siteId + "/?field=" + field.getClass(),
			multiple: !field.getOption("cardinality") || field.getOption("cardinality") > 1,
			allowedExtensions: field.getOption("allowedExtensions"),
			template: field.$(".file-uploader").html(),
			fileTemplate: $.trim(field.$(".previewsTemplate").html()),
			listElement: field.$(".previews").get(0),
			onComplete: function(id, fileName, response) {
				var $item = field.$(".previews").children().filter(function(i) {
					return this.qqFileId == id;
				});
				if (response.success) {
					this.showMessage(null);
					while (field.getOption("cardinality") && field.getOption("cardinality") < field.getCountUploaded()) {
						$item.siblings().first().remove();
					}
					$item.html(response.success.preview + "<input type=\"hidden\" name=\"" +field.getName()+ "[]\" value=\"" +response.success.id+ "\"/>");
				} else {
					$item.remove();	
				}
				if (field.fileUploader.getInProgress() == 0 && field.getCountUploaded() > 0) {
					field.trigger("uploadComplete");
				}
			},
			showMessage: function(message) {
				if (message && message.msg) {
					message = message.msg;
				}
				field.error(message);
			}
		});
		
		this.$(".previews").on("click", ".delete", function() {
			$(this).closest("li").remove();
		});
	},
	
	getCountUploaded: function() {
		return this.$(".previews .qq-upload-success").length;
	}
});