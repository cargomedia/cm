<input type="text" class="{$class}" name="{$name}" />
{if $value}
	{foreach $value as $valueItem}
		{$prePopulate[] = $field->getSuggestion($valueItem, $render)}
	{/foreach}
{/if}
{if !empty($prePopulate)}
	<input type="hidden" class="prePopulate" value="{$prePopulate|@json_encode|escape}" />
{/if}
