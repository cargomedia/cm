<dl>
  {foreach $todoList->getItems() as $todo}
    <dt>{$todo->getTitle()} ({$todo->getState()})</dt>
    <dd>
      {$todo->getDescription()}
    </dd>
  {/foreach}
</dl>
