{$prePopulate = []}
{if $value}
	{foreach $value as $valueItem}
		{$prePopulate[] = $field->getSuggestion($valueItem, $render)}
	{/foreach}
{/if}
{tag el="input" name=$name type="text" class="textinput {$class}" data-pre-populate="{$prePopulate|@json_encode}" data-placeholder=$placeholder}
