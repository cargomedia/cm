{$showColumns = $optionList|count > 4}
<ul id="{$id}" class="{$class} {if $showColumns}columns{/if}">
{foreach $optionList as $itemValue => $itemLabel}
	<li class="set-item {$name}-value-{$itemValue} {if $showColumns}column4{/if}">
		<input type="checkbox" id="{$id}-{$itemValue}" name="{$name}[]" value="{$itemValue|escape}" {if $value && in_array($itemValue, $value)}checked{/if} />
		<label for="{$id}-{$itemValue}" class="{$name}-label-{$itemValue}">
			{if $translate}
				{translate "{$translatePrefix}{$itemLabel}"|escape}
			{else}
				{$itemLabel|escape}
			{/if}
		</label>
	</li>
{/foreach}
</ul>
