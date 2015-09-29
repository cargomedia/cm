/**
 * @class CM_FormField_Integer
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Integer = CM_FormField_Abstract.extend({

  _class: 'CM_FormField_Integer',

  _$noUiHandle: null,

  ready: function() {
    var field = this;
    var $input = this.$('input');
    var $slider = this.$('.noUiSlider');
    var $sliderValue = this.$('.noUiSlider-value');

    $slider.noUiSlider({
      range: {min: field.getOption('min'), max: field.getOption('max')},
      start: $input.val(),
      step: field.getOption('step'),
      handles: 1,
      behaviour: 'tap'
    });
    $slider.on('slide set', function(e, val) {
      val = parseInt(val);
      $input.val(val);
      $sliderValue.html(val);
      field._onChange();
    });

    this._$noUiHandle = $slider.find('.noUi-handle');
    this._$noUiHandle.attr('tabindex', '0');

    $input.watch('disabled', function(propName, oldVal, newVal) {
      if (false === newVal) {
        $slider.removeAttr('disabled');
        field._$noUiHandle.attr('tabindex', '0');
      } else {
        $slider.attr('disabled', 'disabled');
        field._$noUiHandle.attr('tabindex', '-1');
      }
    });

    this.bindJquery($(window), 'keydown', this._onKeyDown);

    this.on('destruct', function() {
      $input.unwatch('disabled');
    });
  },

  getValue: function() {
    return this.getInputValue();
  },

  /**
   * @param {Number} value
   */
  setValue: function(value) {
    this.$('.noUiSlider').val(value);
    this._onChange();
  },

  _onChange: function() {
    this.trigger('change');
  },

  _onKeyDown: function(event) {
    if (this._$noUiHandle.is(':focus')) {
      if (event.which === cm.keyCode.LEFT || event.which === cm.keyCode.DOWN) {
        this.setValue(this.getValue() - this.getOption('step'));
        event.preventDefault();
      }
      if (event.which === cm.keyCode.RIGHT || event.which === cm.keyCode.UP) {
        this.setValue(this.getValue() + this.getOption('step'));
        event.preventDefault();
      }

    }
  }
});
