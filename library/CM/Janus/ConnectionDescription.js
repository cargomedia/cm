/**
 * @class CM_Janus_ConnectionDescription
 * @extends Backbone.Model
 */
var CM_Janus_ConnectionDescription = Backbone.Model.extend({

  _class: 'CM_Janus_ConnectionDescription',

  /**
   * @returns {{key: <String>, type: <Number>}}
   */
  getChannel: function() {
    return this.get('channel');
  },
  
  /**
   * @returns {{webSocketAddress: <String>, iceServers: <Array>}}
   */
  getServer: function() {
    var server = this.get('server');
    server.webSocketAddress += '?' + jQuery.param({
      context: JSON.stringify(cm.getContext())
    });
    return server;
  },
  
  toJSON: function() {
    return _.extend(this.getServer(), this.getChannel());
  },

  /**
   * @param {*} description
   * @returns {Boolean}
   */
  equals: function(description) {
    return description instanceof CM_Janus_ConnectionDescription && _.isEqual(this.toJSON(), description.toJSON());
  }
});
