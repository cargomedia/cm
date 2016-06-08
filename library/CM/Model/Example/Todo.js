/**
 * @class CM_Model_Example_Todo
 * @extends CM_Model_Abstract
 */
var CM_Model_Example_Todo = CM_Model_Abstract.extend({

  /** @type {String} */
  _class: 'CM_Model_Example_Todo',


  _stateNames: ['pending', 'progress', 'cancelled', 'done'],

  /**
   * @returns {String}
   */
  getStateName: function() {
    return this._stateNames[this.get('state')];
  },

  /**
   * @returns {Number}
   */
  getStateNext: function() {
    var state = this.get('state');
    return state + 1 >= this._stateNames.length ? 0 : state + 1;
  }
});
