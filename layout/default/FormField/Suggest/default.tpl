{if $value}
	{foreach $value as $valueItem}
		{$prePopulate[] = $field->getSuggestion($valueItem, $render)}
	{/foreach}
{/if}
{tag el="input" name=$name type="text" class="textinput {$class}" data-prePopulate="{if !empty($prePopulate)}{$prePopulate|@json_encode}{/if}" data-placeholder=$placeholder}
