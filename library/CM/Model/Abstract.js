/**
 * @class CM_Model_Abstract
 * @extends Backbone.Model
 */
var CM_Model_Abstract = Backbone.Model.extend({

  _class: 'CM_Model_Abstract',

  idAttribute: '_compoundId',

  initialize: function() {
    var _id = this.get('_id');
    if (!$.isPlainObject(_id)) {
      throw new Error('`_id` field must be a hash object');
    }
    var idParts = _.values(_id).concat(this.get('_type'));
    this.set('_compoundId', idParts.join('-'));
  },

  /**
   * @returns {Object}
   */
  toJSON: function() {
    return {_id: this.get('_id'), _type: this.get('_type'), _class: this._class};
  }
});
