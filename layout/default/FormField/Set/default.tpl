<ul id="{$id}" class="{$class} columns">
{foreach $optionList as $itemValue => $itemLabel}
	<li class="set-item {$name}_value_{$itemValue} column3">
		<input type="checkbox" id="{$id}-{$itemValue}" name="{$name}[]" value="{$itemValue|escape}" {if $value && in_array($itemValue, $value)} checked="checked"{/if} />
		<label for="{$id}-{$itemValue}" class="{$name}_label_{$itemValue}">
			{if $translate}
				{translate "{$translatePrefix}{$itemLabel}"|escape}
			{else}
				{$itemLabel|escape}
			{/if}
		</label>
	</li>
{/foreach}
</ul>
