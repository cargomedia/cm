/**
 * @class CM_FormField_Integer
 * @extends CM_FormField_Float
 */
var CM_FormField_Integer = CM_FormField_Float.extend({

  _class: 'CM_FormField_Integer',

  /** @type {jQuery|Null} */
  _$noUiHandle: null,

  /** @type {Element|Null} */
  _slider: null,

  /** @type {String} **/
  display: null,

  ready: function() {
    if (this.display === 'slider') {
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
    }
  },

  getValue: function() {
    if (this.display === 'slider') {
      if (this._slider) {
        return +this._slider.noUiSlider.get();
      }
    } else {
      return CM_FormField_Integer.__super__.getValue.apply(this, arguments);
    }
  },

  /**
   * @param {Number} value
   */
  setValue: function(value) {
    if (this.display === 'slider') {
      if (this._slider) {
        this._slider.noUiSlider.set(value);
      }
    } else {
      CM_FormField_Integer.__super__.setValue.apply(this, arguments);
    }
  },

  getEnabled: function() {
    if (this.display === 'slider') {
      if (this._slider) {
        return !this._slider.getAttribute('disabled');
      }
    } else {
      return CM_FormField_Integer.__super__.getEnabled.apply(this, arguments);
    }
  },

  /**
   * @param {Boolean} enabled
   */
  setEnabled: function(enabled) {
    if (this.display === 'slider') {
      if (!this._slider) {
        return;
      }
      if (enabled) {
        this._slider.removeAttribute('disabled');
      } else {
        this._slider.setAttribute('disabled', 'disabled');
      }
    } else {
      this.getInput().prop("disabled", !enabled)
    }
  },

  enableTriggerChangeOnInput: function() {
    if (this.display === 'default') {
      CM_FormField_Integer.__super__.enableTriggerChangeOnInput.apply(this, arguments);
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
