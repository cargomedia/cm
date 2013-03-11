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
		this.on('change', function() {
			$('.iconBox').css('background-color', this.getField('colorBackground').getValue());
			$('.iconBox .icon').css('text-shadow', this._getShadowValue());
			$('.iconBox .icon').css('color', this.getField('color').getValue());
			$('.iconBox .icon').css('font-size', this.getField('sizeSlider').getValue() + "px");
			this.$('.iconCss').html('background-color: ' + $(".iconBox").css('background-color') + ';<br />' + $(".iconBox .icon").first().attr('style').replace(/; /g, ";<br />"));
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
			this.getField('shadowColor').getValue() || '#fff',
			this.getField('shadowX').getValue() + 'px',
			this.getField('shadowY').getValue() + 'px',
			this.getField('shadowBlur').getValue() + 'px'
		].join(' ');
	}


});