<ul id="{$id}" class="{$class} clearfix">
{foreach $optionList as $itemValue => $itemLabel}
	<li class="list_label {$name}_value_{$itemValue}" {if $colSize}style="width: {$colSize};"{/if}>
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
