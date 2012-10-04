/**
 * @class CM_FormField_Location
 * @extends CM_FormField_SuggestOne
 */
var CM_FormField_Location = CM_FormField_SuggestOne.extend({
	_class: 'CM_FormField_Location',

	getDistanceField: function() {
		if (!this.getOption("distanceName")) {
			return null;
		}
		return this.getForm().getField(this.getOption("distanceName"));
	},
	
	onChange: function(items) {
		if (this.getDistanceField()) {
			var distanceEnabled = false;
			if (items.length > 0) {
				distanceEnabled = items[0].id.split(".")[0] >= this.getOption("distanceLevelMin");
			}
			this.getDistanceField().$("input").prop("disabled", !distanceEnabled);
		}
	}
});