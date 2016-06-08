/**
 * @class CM_Component_Example_TodoList
 * @extends CM_Component_Abstract
 */
var CM_Component_Example_TodoList = CM_Component_Abstract.extend({

  /** @type {CM_Paging_Example_Toto} */
  _todoList: null,

  /** @type {jQuery} */
  _$target: null,

  _class: 'CM_Component_Example_TodoList',

  events: {
    'click .delete': function(event) {
      var $icon = $(event.currentTarget);
      var todo = this._todoList.get($icon.closest('li').data('id'));
      return this.ajax('delete', {todo: todo});
    },

    'click .changeState': function(event) {
      var $icon = $(event.currentTarget);
      var todo = this._todoList.get($icon.closest('li').data('id'));
      return this.ajax('changeState', {todo: todo, state: todo.getStateNext()});
    }
  },

  ready: function() {
    this.$target = this.$('.todo-list');

    this.listenTo(this._todoList, 'add', this.render);
    this.listenTo(this._todoList, 'remove', this.render);
    this.listenTo(this._todoList, 'change', this.render);

    this.render();
  },

  render: function() {
    var html = this.renderTemplate('tpl-todo-list', {
      todoList: this._todoList
    });
    this.$target.html(html);
  }
});
