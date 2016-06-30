/**
 * @class CM_Janus_ConnectionDescription
 * @extends Backbone.Model
 */
var CM_Janus_ConnectionDescription = Backbone.Model.extend({

  _class: 'CM_Janus_ConnectionDescription',

  /**
   * @returns {CM_StreamChannel_Definition}
   */
  getChannel: function() {
    return this.get('channel');
  },

  /**
   * @returns {CM_Janus_Server}
   */
  getServer: function() {
    return this.get('server');
  },

  toJSON: function() {
    return {
      channel: this.getChannel().toJSON(),
      server: this.getServer().toJSON()
    };
  },

  /**
   * @param {*} description
   */
  update: function(description) {
    if (description instanceof CM_Janus_ConnectionDescription && !this.equals(description)) {
      this.getServer().set(description.getServer().toJSON());
      this.getChannel().set(description.getChannel().toJSON());
    }
  },

  /**
   * @param {*} description
   * @returns {Boolean}
   */
  equals: function(description) {
    return description instanceof CM_Janus_ConnectionDescription && _.isEqual(this.toJSON(), description.toJSON());
  }
});
