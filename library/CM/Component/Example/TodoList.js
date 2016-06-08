/**
 * @class CM_Component_Example_TodoList
 * @extends CM_Component_Abstract
 */
var CM_Component_Example_TodoList = CM_Component_Abstract.extend({

  /** @type {CM_Paging_Example_Toto} */
  _todoList: null,

  /** @type {CM_Form_Example_Todo} */
  _form: null,

  /** @type {jQuery} */
  _$target: null,

  /** @type {jQuery} */
  _$buttonAdd: null,

  _class: 'CM_Component_Example_TodoList',

  events: {
    'click .delete': function(event) {
      var $el = $(event.currentTarget);
      var todo = this._todoList.get($el.closest('li').data('id'));
      return this.ajax('delete', {todo: todo});
    },

    'click .changeState': function(event) {
      var $el = $(event.currentTarget);
      var todo = this._todoList.get($el.closest('li').data('id'));
      return this.ajax('changeState', {todo: todo, state: todo.getStateNext()});
    },

    'click h4': function(event) {
      var $el = $(event.currentTarget);
      var todo = this._todoList.get($el.closest('li').data('id'));
      this._form.fill(todo);
      this._form.show();
      this._$buttonAdd.hide();
    },

    'click .add': function() {
      this._form.show();
      this._$buttonAdd.hide();
    },

    'click .cancel': function() {
      this._form.hide();
      this._$buttonAdd.show();
    }
  },

  childrenEvents: {
    'CM_Form_Example_Todo complete': function() {
      this._form.clear();
      this._form.hide();
      this._$buttonAdd.show();
    }
  },


  ready: function() {
    this._$target = this.$('.todo-list');
    this._$buttonAdd = this.$('.add');
    this._form = this.findChild('CM_Form_Example_Todo');

    this.listenTo(this._todoList, 'add', this.render);
    this.listenTo(this._todoList, 'remove', this.render);
    this.listenTo(this._todoList, 'change', this.render);

    this.render();
  },

  render: function() {
    var html = this.renderTemplate('tpl-todo-list', {
      todoList: this._todoList
    });
    this._$target.html(html);
  }
});
