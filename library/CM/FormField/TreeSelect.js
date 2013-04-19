/**
 * @class CM_FormField_TreeSelect
 * @extends CM_FormField_Abstract
 */
var CM_FormField_TreeSelect = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_TreeSelect',

	events: {
		'click .selectNode': function(event) {
			this.selectNode($(event.currentTarget));
		},
		'click .toggleSubtree': function(event) {
			this.toggleSubtree($(event.currentTarget));
		},
		'click .toggleWindow': 'toggleWindow',
		'click .unselectNode': 'unselectNode'
	},

	$input: null,
	$selector: null,
	$options: null,
	defaultSelectorLabel: null,

	ready: function() {
		var handler = this;
		this.$input = this.$('input');
		this.$selector = this.$('.selector');
		this.$options = this.$('.options');
		this.defaultSelectorLabel = this.$selector.text();

		this.on('change', function() {
			handler.$('.selected').removeClass('selected');
			var $item = handler.getSelectedItem();
			if ($item.length) {
				$item.addClass('selected');
				handler.$selector.find('.label').text($item.data('path'));
			} else {
				handler.$selector.find('.label').text(handler.defaultSelectorLabel);
			}
		});

		this.$input.watch("disabled", function(propName, oldVal, newVal) {
			handler.$selector.toggleClass("disabled", newVal);
		});

		this.selectNode(this.getSelectedItem());
	},

	toggleWindow: function() {
		this.$options.toggleModal();
		if (this.$options.is(':visible')) {
			this.getSelectedItem().parent().parents('.CM_FormField_TreeSelect li').addClass('active').find('> .toggleSubtree').addClass('active');
		}
	},

	close: function() {
		this.$options.toggleModalClose();
	},

	/**
	 * @param {jQuery} $selectedItem
	 */
	selectNode: function($selectedItem) {
		this.$input.val($selectedItem ? $selectedItem.data('id') : null);
		this.trigger('change');
		this.close();
	},

	/**
	 * @param {jQuery} $toggleSubtree
	 */
	toggleSubtree: function($toggleSubtree) {
		$toggleSubtree.closest('li').toggleClass('active');
		$toggleSubtree.toggleClass('active');
	},

	unselectNode: function() {
		this.$input.val(null);
		this.trigger('change');
		this.close();
	},

	getSelectedItem: function() {
		return this.$('.node[data-id="' + this.getValue() + '"]');
	}
});
