{button_link class='getLocation current-location' icon='crosshair' title={translate 'Get Current Location'}}
<input type="text" class="textinput {$class}" name="{$name}" />
{if $value}
	{foreach $value as $valueItem}
		{$prePopulate[] = $field->getSuggestion($valueItem, $render)}
	{/foreach}
{/if}
{if !empty($prePopulate)}
	<input type="hidden" class="prePopulate" value="{$prePopulate|@json_encode|escape}" />
{/if}
