<input type="text" class="textinput {$class}" name="{$name}" />
{if !empty($prePopulate)}
	<input type="hidden" class="prePopulate" value="{$prePopulate|@json_encode|escape}" />
{/if}
