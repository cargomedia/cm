/**
 * @class CM_Form_ExampleIcon
 * @extends CM_Form_Abstract
 */
var CM_Form_ExampleIcon = CM_Form_Abstract.extend({

	/** @type String */
	_class: 'CM_Form_ExampleIcon',

	events: {
		'click .iconBox': function(event) {
			this.selectIcon($(event.currentTarget));
		}
	},

	ready: function() {
		var self = this;
		this.on('change', function() {
			self._updateCss();
		});
		this.$('input[type="text"]').changetext(function() {
			self.trigger('change')
		});
		this.trigger('change');
	},

	/**
	 * @param {jQuery} $icon
	 */
	selectIcon: function($icon) {
		$icon.addClass('active').siblings().removeClass('active');
		this.$('.iconMarkup').text('<span class="' + $icon.find('.label').text() + '"></span>');
	},

	_getShadowValue: function() {
		return [
			this.getField('shadowColor').getValue() || '#fff', this.getField('shadowX').getValue() + 'px', this.getField('shadowY').getValue() + 'px', this.getField('shadowBlur').getValue() + 'px'
		].join(' ');
	},

	_updateCss: function() {
		this.$('.iconBox').css('background-color', this.getField('colorBackground').getValue());
		this.$('.iconBox .icon').css('text-shadow', this._getShadowValue());
		this.$('.iconBox .icon').css('color', this.getField('color').getValue());
		this.$('.iconBox .icon').css('font-size', this.getField('sizeSlider').getValue() + "px");
		this.$('.iconCss').html('background-color: ' + this.$(".iconBox").css('background-color') + ';<br />' + this.$(".iconBox .icon").first().attr('style').replace(/; /g, ";<br />"));
	}
});
