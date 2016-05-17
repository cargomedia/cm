/**
 * @class CM_Model_Abstract
 * @extends Backbone.Model
 */
var CM_Model_Abstract = Backbone.Model.extend({
  toJSON: function() {
    return {id: this.get('id'), type: this.get('_type'), _type: this.get('_type')};
  }
});
