<div class="todo-list"></div>

<div class="todo-list-edit">
  {form name="CM_Form_Example_Todo"}
  {formField name='title' label='Title'}
  {formField name='description' label='Description'}
  {formField name='state' label='State'}
  {formAction action='Todo_Save' label='Save' theme='default'}
  {/form}
</div>

{literal}
  <script type="text/template" class="tpl-todo-list">
    <ul>
      [[ todoList.each(function(todo) { ]]
      <li data-id="[[- todo.id ]]">
        <h4>[[- todo.get('title') ]]</h4>
        <div class="icons">
          <span class="icon icon-trash delete"></span>
          <span class="icon icon-tag changeState [[- todo.getStateName() ]]"></span>
        </div>
        [[- todo.get('description') ]]
      </li>
      [[ }) ]]
    </ul>
  </script>
{/literal}
