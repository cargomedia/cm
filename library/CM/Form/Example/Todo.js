/**
 * @class CM_Form_Example_Todo
 * @extends CM_Form_Abstract
 */
var CM_Form_Example_Todo = CM_Form_Abstract.extend({

  /** @type {String} */
  _class: 'CM_Form_Example_Todo',

  show: function() {
    this.$el.show();
  },

  hide: function() {
    this.$el.hide();
  },

  clear: function() {
    _.each(this.getFields(), function(field) {
      if (field instanceof CM_FormField_Set_Select) {
        field.setValue(0);
      } else {
        field.setValue('');
      }
    });
  },

  /**
   * @param {CM_Model_Example_Toto} todo
   */
  fill: function(todo) {
    _.each(this.getFields(), function(field) {
      var value = field.getName() !== 'todoId' ? todo.get(field.getName()) : todo.get('id');
      field.setValue(value);
    });
  }
});
