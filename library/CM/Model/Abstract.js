/**
 * @class CM_Model_Abstract
 * @extends Backbone.Model
 */
var CM_Model_Abstract = Backbone.Model.extend({

  _class: 'CM_Model_Abstract',

  idAttribute: '_compoundId',

  initialize: function() {
    this.set('_compoundId', this.get('_id').id + '-' + this.get('_type'));
  },

  toJSON: function() {
    return {_id: this.get('_id'), _type: this.get('_type'), _class: this._class};
  }
});
