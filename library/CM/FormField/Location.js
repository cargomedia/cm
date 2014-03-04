/**
 * @class CM_FormField_Location
 * @extends CM_FormField_SuggestOne
 */
var CM_FormField_Location = CM_FormField_SuggestOne.extend({
  _class: 'CM_FormField_Location',

  events: {
    'click .detectLocation': 'detectLocation'
  },

  getDistanceField: function() {
    if (!this.getOption("distanceName")) {
      return null;
    }
    return this.getForm().getField(this.getOption("distanceName"));
  },

  onChange: function(items) {
    if (this.getDistanceField()) {
      var distanceEnabled = false;
      if (items.length > 0) {
        distanceEnabled = items[0].id.split(".")[0] >= this.getOption("distanceLevelMin");
      }
      this.getDistanceField().$("input").prop("disabled", !distanceEnabled);
    }
  },

  detectLocation: function() {
    if (!'geolocation' in navigator) {
      cm.error.triggerThrow('Geolocation support unavailable');
    }
    this.$('.detect-location').addClass('waiting');

    var self = this;
    var deferred = $.Deferred();
    navigator.geolocation.getCurrentPosition(deferred.resolve, deferred.reject);

    deferred.then(function(position) {
      self._lookupCoordinates(position.coords.latitude, position.coords.longitude);
    }, function(error) {
      cm.error.trigger('Unable to detect location: ' + error.message);
    });
    deferred.always(function() {
      self.$('.detect-location').removeClass('waiting');
    });

    return deferred;
  },

  /**
   * @param {Number} lat
   * @param {Number} lon
   */
  _lookupCoordinates: function(lat, lon) {
    this.ajax('getSuggestionByCoordinates', {lat: lat, lon: lon, levelMin: this.getOption('levelMin'), levelMax: this.getOption('levelMax')}, {
      success: function(data) {
        if (data) {
          this.setValue(data);
        }
      }
    });
  }
});
