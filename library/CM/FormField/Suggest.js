/**
 * @class CM_FormField_Suggest
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Suggest = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Suggest',

	ready: function() {
		var field = this;
		var prePopulate = this.$(".prePopulate");
		prePopulate = prePopulate.length ? JSON.parse(prePopulate.val()) : null;
		var $input = this.$('input[type="text"]');

		$input.select2({
			tags: null,
			allowClear: true,
			maximumSelectionSize: field.getOption("cardinality") + 1,
			formatResult: this.getItem,
			formatSelection: this.getItemSelected,
			escapeMarkup: function(item) {
				return item;
			},
			query: function(options) {
				var _options = options;
				field.ajax('getSuggestions', {'term': options.term, 'options': field.getOptions()}, {
					success: function(results) {
						_options.callback({
							results: results
						});
					}
				});
			},
			createSearchChoice: function(term, data) {
				if (field.getOption("enableChoiceCreate")) {
					if ($(data).filter(function() {
						return this.name.localeCompare(term) === 0;
					}).length === 0) {
						return {id: term, name: term, new: 1};
					}
				}
			}
		}).select2('data', prePopulate);

		$input.on("change", function(e) {
			if (!_.isUndefined(e.added)) {
				field.onAdd(e.added);
				field.trigger('add', e.added);
			}
			if (!_.isUndefined(e.removed)) {
				field.onAdd(e.removed);
				field.trigger('delete', e.removed);
			}
			field.onChange($input.select2("data"));
		});

		if (this.getOption("cardinality") == 1) {
			$input.on("open", function(e) {
				$input.select2('data', null);
			});
		}

		this.getForm().$().bind("reset", function() {
			$input.select2('data', null);
		});

		this.onChange($input.select2("data"));
	},

	getItem: function(item) {
		var output = _.escape(item.name);
		if (item.description) {
			output += "<small>" + _.escape(item.description) + "</small>";
		}
		if (item.img) {
			output = "<img src=\"" + item.img + "\" /> " + output;
		}
		return output;
	},

	getItemSelected: function(item) {
		var output = _.escape(item.name);
		if (item.img) {
			output = "<img src=\"" + item.img + "\" /> " + output;
		}
		return output;
	},

	onAdd: function(item) {
	},

	onDelete: function(item) {
	},

	onChange: function(items) {
	}
});
