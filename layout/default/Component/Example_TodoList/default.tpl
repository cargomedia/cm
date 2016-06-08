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
    <dl>
      [[ todoList.each(function(todo) { ]]
      <dt>[[- todo.get('title') ]] ([[- todo.get('state') ]])</dt>
      <dd>
        [[- todo.get('description') ]]
      </dd>
      [[ }) ]]
    </dl>
  </script>
{/literal}
