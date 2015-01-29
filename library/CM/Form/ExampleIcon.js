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
      this._updateCss();
    }, this);
    var self = this;
    setTimeout(function() {
      self._updateCss();
    }, 0);
  },

  /**
   * @param {jQuery} $icon
   */
  selectIcon: function($icon) {
    $icon.addClass('active').siblings().removeClass('active');
    this.$('.iconMarkup').text('<span class="icon icon-' + $icon.find('.label').text() + '"></span>');
  },

  _getShadowValue: function() {
    return [
      this.getField('shadowColor').getValue() || '#fff', this.getField('shadowX').getValue() + 'px', this.getField('shadowY').getValue() + 'px', this.getField('shadowBlur').getValue() + 'px'
    ].join(' ');
  },

  _updateCss: function() {
    var appliedStyles = {};
    appliedStyles['background-color'] = this.$('.iconBox').css('background-color', this.getField('colorBackground').getValue()).css('background-color');
    appliedStyles['text-shadow'] = this.$('.iconBox .icon').css('text-shadow', this._getShadowValue()).css('text-shadow');
    appliedStyles['color'] = this.$('.iconBox .icon').css('color', this.getField('color').getValue()).css('color');
    appliedStyles['font-size'] = this.$('.iconBox .icon').css('font-size', this.getField('sizeSlider').getValue() + "px").css('font-size');

    this.$('.iconCss').html(_.reduce(appliedStyles, function(memo, value, key) {
      return memo + key + ': ' + value + ';<br />';
    }, ''));
  }
});
