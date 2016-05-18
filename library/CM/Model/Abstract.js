/**
 * @class CM_Model_Abstract
 * @extends Backbone.Model
 */
var CM_Model_Abstract = Backbone.Model.extend({

  idAttribute: '_compoundId',

  initialize: function() {
    this.set('_compoundId', this.get('_id').id + '-' + this.get('_type'));
  },

  toJSON: function() {
    var json = {_id: this.get('_id'), _type: this.get('_type')};
    if (this.has('id')) {
      json['id'] = this.get('id');
    }
    return json;
  }
});
