/**
 * @class CM_Form_ExampleIcon
 * @extends CM_Form_Abstract
 */
var CM_Form_ExampleIcon = CM_Form_Abstract.extend({

	/** @type String */
	_class: 'CM_Form_ExampleIcon',

	events: {
		'click .iconBox': 'selectIcon'
	},

	ready: function() {
		this.on('change', function() {
			$('.iconBox').css('background-color', this.getField('colorBackground').getValue());
			$('.iconBox .icon').css('text-shadow', this._getShadowValue());
			$('.iconBox .icon').css('color', this.getField('color').getValue());
			$('.iconBox .icon').css('font-size', this.getField('sizeSlider').getValue() + "px");
			this.$('.iconCss').text('background-color: ' + $(".iconBox").css('background-color') + '; \n' + $(".iconBox .icon").first().attr('style').replace(/; /g, ";\n"));
		});
		this.trigger('change');
	},

	selectIcon: function(event) {
		var target = $(event.currentTarget);
		target.addClass('active').siblings().removeClass('active');
		this.$('.iconMarkup').text('<span class="icon ' + target.find('.label').html() + '"></span>');
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