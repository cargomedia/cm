/**
 * @class CM_FormField_Suggest
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Suggest = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_Suggest',

	/** @type {jQuery} */
	_$input: null,

	/** @type {jqXHR} */
	_request: null,

	ready: function() {
		var field = this;
		var cardinality = this.getOption("cardinality");
		this._$input = this.$('input[type="text"]');
		var prePopulate = this._$input.data('pre-populate');

		this._$input.removeClass('textinput');
		this._$input.select2({
			width: 'off',
			tags: null,
			dropdownCssClass: this.$el.attr('class'),
			allowClear: true,
			openOnEnter: false,
			maximumSelectionSize: cardinality,
			formatResult: this._formatItem,
			formatSelection: this._formatItemSelected,
			escapeMarkup: function(item) {
				return item;
			},
			query: function(options) {
				if (this._request) {
					this._request.abort();
				}
				this._request = field.ajax('getSuggestions', {'term': options.term, 'options': field.getOptions()}, {
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
						return {'id': term, 'name': term, 'new': 1};
					}
				}
			},
			formatSelectionTooBig: null
		}).select2('data', prePopulate);
		this.$('.select2-choices').addClass('textinput');

		this._$input.on("change", function(e) {
			if (!_.isUndefined(e.added)) {
				var items = field._$input.select2("data");
				if (cardinality && items.length > cardinality) {
					items.pop();
					field._$input.select2('data', items);
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
			field.onChange(field._$input.select2("data"));
			field.trigger('change');
		});

		if (1 == cardinality) {
			this._$input.on('select2-open', function(e) {
				field._$input.select2('data', null);
			});
		}

		this._$input.on('select2-open', function() {
			field.trigger('open');
		});

		this._$input.on('select2-close', function() {
			field.trigger('close');
		});

		this.getForm().$el.bind("reset", function() {
			field._$input.select2('data', null);
		});

		this.onChange(this._$input.select2("data"));
		this.trigger('change');
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

	blur: function() {
		this.setTimeout(function() {
			this.$('input.select2-input').blur();
		}, 10);
	},

	/**
	 * @param {Object} item
	 * @return String
	 */
	_formatItem: function(item) {
		var cssClass = 'suggestItem';
		if (item['class']) {
			cssClass += ' ' + _.escape(item['class']);
		}
		var output = '<div class="' + cssClass + '">';
		if (item['img']) {
			output += '<div class="suggestItem-image"><img src="' + item['img'] + '" /></div>';
		}
		output += '<span class="suggestItem-name">' + _.escape(item['name']) + '</span>';
		if (item['description']) {
			output += '<small class="suggestItem-description">' + _.escape(item['description']) + '</small>';
		}
		output += '</div>';
		return output;
	},

	/**
	 * @param {Object} item
	 * @return String
	 */
	_formatItemSelected: function(item) {
		var output = _.escape(item['name']);
		if (item['img']) {
			output = '<div class="suggestItem-image"><img src="' + item['img'] + '" /></div>' + output;
		}
		return output;
	}
});
