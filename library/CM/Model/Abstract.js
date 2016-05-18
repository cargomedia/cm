/**
 * @class CM_Model_Abstract
 * @extends Backbone.Model
 */
var CM_Model_Abstract = Backbone.Model.extend({

  idAttribute: '_compoundId',

  initialize: function() {
    this.set('_compoundId', this.get('id') + '-' + this.get('_type'));
  },

  toJSON: function() {
    return {id: this.get('id'), _type: this.get('_type')};
  }
});
