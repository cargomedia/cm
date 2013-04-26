/**
 * @class CM_FormField_Suggest
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Suggest = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Suggest',

	$input: null,

	ready: function() {
		var field = this;
		var cardinality = this.getOption("cardinality");
		this.$input = this.$('input[type="text"]');
		this.$input.removeClass('textinput');

		this.$input.select2({
			tags: null,
			allowClear: true,
			maximumSelectionSize: cardinality,
			formatResult: this._formatItem,
			formatSelection: this._formatItemSelected,
			escapeMarkup: function(item) {
				return item;
			},
			query: function(options) {
				field.ajax('getSuggestions', {'term': options.term, 'options': field.getOptions()}, {
					success: function(results) {
						options.callback({
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
			},
			formatSelectionTooBig: null
		}).select2('data', this._getPrePopulateValue());

		this.$input.on("change", function(e) {
			if (!_.isUndefined(e.added)) {
				var items = field.$input.select2("data");
				if (cardinality && items.length > cardinality) {
					items.pop();
					field.$input.select2('data', items);
					field.$el.popover('destroy').popoverInfo(cm.language.get('You can only select {$cardinality} items.', {'cardinality': cardinality}), 2000);
					return false;
				}
				field.onAdd(e.added);
				field.trigger('add', e.added);
			}
			if (!_.isUndefined(e.removed)) {
				field.onDelete(e.removed);
				field.trigger('delete', e.removed);
			}
			field.onChange(field.$input.select2("data"));
		});

		if (1 == cardinality) {
			this.$input.on("open", function(e) {
				field.$input.select2('data', null);
			});
		}

		this.getForm().$().bind("reset", function() {
			field.$input.select2('data', null);
		});

		this.onChange(this.$input.select2("data"));
	},

	/**
	 * @param {Object} item
	 */
	onAdd: function(item) {
	},

	/**
	 * @param {Object} item
	 */
	onDelete: function(item) {
	},

	/**
	 * @param {Object[]} items
	 */
	onChange: function(items) {
	},

	/**
	 * @return {Object[]|null}
	 */
	_getPrePopulateValue: function() {
		var prePopulate = this.$input.attr('data-prePopulate');
		if (prePopulate && prePopulate.length) {
			return JSON.parse(prePopulate);
		}
		return null;
	},

	/**
	 * @param {Object} item
	 * @return String
	 */
	_formatItem: function(item) {
		var output = _.escape(item.name);
		if (item.description) {
			output += '<small>' + _.escape(item.description) + '</small>';
		}
		if (item.img) {
			output = '<img src="' + item.img + '" /> ' + output;
		}
		return output;
	},

	/**
	 * @param {Object} item
	 * @return String
	 */
	_formatItemSelected: function(item) {
		var output = _.escape(item.name);
		if (item.img) {
			output = '<img src="' + item.img + '" /> ' + output;
		}
		return output;
	}
});
