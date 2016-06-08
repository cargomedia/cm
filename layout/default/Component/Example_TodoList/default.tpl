<div class="todo-list"></div>

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
