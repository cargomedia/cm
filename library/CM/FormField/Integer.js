var CM_FormField_Abstract = require('CM/FormField/Abstract');

/**
 * @class CM_FormField_Integer
 * @extends CM_FormField_Abstract
 */
var CM_FormField_Integer = CM_FormField_Abstract.extend({

  _class: 'CM_FormField_Integer',

  /** @type {jQuery} */
  _$noUiHandle: null,

  /** @type {Element} */
  _slider: null,

  ready: function() {
    var field = this;
    var $slider = this.$('.noUiSlider');
    var $sliderValue = this.$('.noUiSlider-value');
    this._slider = $slider[0];

    noUiSlider.create(this._slider, {
      range: {min: field.getOption('min'), max: field.getOption('max')},
      start: $sliderValue.text(),
      step: field.getOption('step'),
      handles: 1,
      behaviour: 'tap'
    });
    this._$noUiHandle = $slider.find('.noUi-handle');
    this._$noUiHandle.attr('tabindex', '0');

    this._slider.noUiSlider.on('update', function(values, handle) {
      var val = parseInt(values[handle]);
      $sliderValue.html(val);
      field._onChange();
    });

    this.bindJquery($(window), 'keydown', this._onKeyDown);
  },

  getInput: function() {
    return this.$('.noUiSlider');
  },

  getValue: function() {
    if (this._slider) {
      return +this._slider.noUiSlider.get();
    }
    return null;
  },

  /**
   * @param {Number} value
   */
  setValue: function(value) {
    this._slider.noUiSlider.set(value);
  },

  getEnabled: function() {
    return !this._slider.getAttribute('disabled');
  },

  /**
   * @param {Boolean} enabled
   */
  setEnabled: function(enabled) {
    if (enabled) {
      this._slider.removeAttribute('disabled');
    } else {
      this._slider.setAttribute('disabled', 'disabled');
    }
  },

  _onChange: function() {
    this.trigger('change');
  },

  _onKeyDown: function(event) {
    if (this._$noUiHandle.is(':focus') && this.getEnabled()) {
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


module.exports = CM_FormField_Integer;