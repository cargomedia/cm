/**
 * @class CM_FormField_Location
 * @extends CM_FormField_SuggestOne
 */
var CM_FormField_Location = CM_FormField_SuggestOne.extend({
  _class: 'CM_FormField_Location',

  events: {
    'click .detectLocation': 'detectLocation'
  },

  ready: function() {
    CM_FormField_SuggestOne.prototype.ready.call(this);

    this.on('change', function() {
      this.updateDistanceField();
    }, this);
    this.updateDistanceField();
  },

  /**
   * @returns {CM_FormField_Integer|Null}
   */
  getDistanceField: function() {
    if (!this.getOption("distanceName")) {
      return null;
    }
    return this.getForm().getField(this.getOption("distanceName"));
  },

  updateDistanceField: function() {
    if (this.getDistanceField()) {
      var distanceEnabled = false;
      var items = this.getValue();
      if (items.length > 0) {
        distanceEnabled = items[0].id.split(".")[0] >= this.getOption("distanceLevelMin");
      }
      this.getDistanceField().$("input").prop("disabled", !distanceEnabled);
    }
  },

  /**
   * @returns {Promise}
   */
  detectLocation: function() {
    if (!'geolocation' in navigator) {
      cm.error.triggerThrow('Geolocation support unavailable');
    }
    this.$('.detect-location').addClass('waiting');

    var self = this;

    return new Promise(function(resolve, reject) {
      navigator.geolocation.getCurrentPosition(resolve, reject);
    })
      .then(function(position) {
        return self._lookupCoordinates(position.coords.latitude, position.coords.longitude);
      })
      .then(function(data) {
        if (data) {
          self.setValue(data);
        }
      })
      .catch(function(error) {
        cm.error.trigger('Unable to detect location: ' + error ? error.message : '');
      })
      .finally(function() {
        self.$('.detect-location').removeClass('waiting');
      });
  },

  /**
   * @param {Number} lat
   * @param {Number} lon
   * @return {Promise}
   */
  _lookupCoordinates: function(lat, lon) {
    return this.ajax('getSuggestionByCoordinates', {lat: lat, lon: lon, levelMin: this.getOption('levelMin'), levelMax: this.getOption('levelMax')});
  }
});
