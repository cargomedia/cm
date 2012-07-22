<ul id="{$id}" class="{$class} clearfix">
{foreach $optionList as $itemValue => $itemLabel}
	<li class="list_label {$name}_value_{$itemValue}" {if $colSize}style="width: {$colSize};"{/if}>
		<label>
			<input type="checkbox" name="{$name}[]" value="{$itemValue|escape}" {if $value && in_array($itemValue, $value)} checked="checked"{/if} />
			<span class="{$name}_label_{$itemValue}">
				{if $translate}
					{translate "{$translatePrefix}{$itemLabel}"|escape}
				{else}
					{$itemLabel|escape}
				{/if}
			</span>
		</label>
	</li>
{/foreach}
</ul>
