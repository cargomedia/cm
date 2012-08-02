/**
 * @class CM_FormField_TreeSelect
 * @extends CM_FormField_Abstract
 */
var CM_FormField_TreeSelect = CM_FormField_Abstract.extend({
	_class: 'CM_FormField_TreeSelect',

	events: {
		'click .unselect': 'unselect',
		'click .selector': 'toggle',
		'click .node': function (event) {
			this.select($(event.currentTarget));
		}
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
	
		this.$('.toggle').click(function () {
			$(this).closest('li').toggleClass('active');
			$(this).toggleClass('active');
		});
	
		this.on('change', function () {
			handler.$('.selected').removeClass('selected');
			var $item = handler.getSelectedItem();
			if ($item.length) {
				$item.addClass('selected');
				handler.$selector.find('.label').text($item.data('path'));
			} else {
				handler.$selector.find('.label').text(handler.defaultSelectorLabel);
			}
		});
	
		this.$input.watch("disabled", function (propName, oldVal, newVal) {
			handler.$selector.toggleClass("disabled", newVal);
		});
	
		this.select(this.getSelectedItem());
	},
	
	toggle: function() {
		this.$options.toggleModal();
		if (this.$options.is(':visible')) {
			this.getSelectedItem().parent().parents('.CM_FormField_TreeSelect li').addClass('active').find('> .toggle').addClass('active');
		}
	},
	
	close: function() {
		this.$options.toggleModalClose();
	},
	
	/**
	 * @param {jQuery} $selectedItem
	 */
	select: function($selectedItem) {
		this.$input.val($selectedItem ? $selectedItem.data('id') : null);
		this.trigger('change');
		this.close();
	},
	
	unselect: function() {
		this.$input.val(null);
		this.trigger('change');
		this.close();
	},
	
	getSelectedItem: function() {
		return this.$('.node[data-id="' + this.getValue()+ '"]');
	}
});