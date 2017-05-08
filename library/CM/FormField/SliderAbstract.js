/**
 * @class CM_FormField_SliderAbstract
 * @extends CM_FormField_Abstract
 */
var CM_FormField_SliderAbstract = CM_FormField_Abstract.extend({

  _class: 'CM_FormField_SliderAbstract',

  /** @type {jQuery} */
  _$noUiHandle: null,

  /** @type {Element} */
  _slider: null,

  /** @type {Array<Number>} */
  sliderStart: null,

  ready: function() {
    var field = this;
    var $slider = this.$('.noUiSlider');
    var slider = this._slider = $slider[0];

    var connectHandles = [true, false];
    for (var i = 0; i < this._getCardinality() - 1; i++) {
      connectHandles.unshift(false);
    }

    noUiSlider.create(this._slider, {
      range: {min: field.getOption('min'), max: field.getOption('max')},
      start: this.sliderStart,
      step: field.getOption('step'),
      connect: connectHandles,
      behaviour: 'tap',
      tooltips: true,
      format: {
        to: function(value) {
          return Math.round(value * 1000) / 1000;
        },
        from: function(value) {
          return value;
        }
      }
    });
    this._$noUiHandle = $slider.find('.noUi-handle');
    this._$noUiHandle.attr('tabindex', '0');

    slider.noUiSlider.on('change', function(values, handle) {
      field._onChange();
    });

    slider.noUiSlider.on('slide', function(values, handle) {
      field._onSlide();
    });

    this.bindJquery($(window), 'keydown', this._onKeyDown);
  },

  getInput: function() {
    return this.$('.noUiSlider');
  },

  /**
   * @returns {Number|Array<Number>|Null}
   */
  getValue: function() {
    var value = null;
    if (this._slider) {
      value = this._slider.noUiSlider.get();
    }
    return value;
  },

  /**
   * @param {Number|Array<Number>} value
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

  /**
   * @return {Number}
   */
  _getCardinality: function() {
    return this.getOption('cardinality');
  },

  _onChange: function() {
    this.trigger('change');
  },

  _onSlide: function() {
    this.trigger('slide');
  },

  _onKeyDown: function(event) {
    if (this._$noUiHandle.is(':focus') && this.getEnabled() && 1 === this._getCardinality()) {
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
