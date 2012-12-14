{if $display === CM_FormField_Set_Select::DISPLAY_RADIOS}
<ul id="{$id}" class="{$class}">
	{foreach $optionList as $itemValue => $itemLabel}
		<li class="list_label {$name}_value_{$itemValue}" {if $colSize}style="width:{$colSize};"{/if}>
			<input id="{$id}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked="checked"{/if} />
			<label for="{$id}-{$itemValue}" class="{$name}_label_{$itemValue}">
				{block name='label'}
					{if $translate}
						{translate "{$translatePrefix}{$itemLabel}"|escape}
					{else}
						{$itemLabel|escape}
					{/if}
				{/block}
			</label>
		</li>
	{/foreach}
</ul>
<br clear="all" />
{/if}

{if $display === CM_FormField_Set_Select::DISPLAY_SELECT}
<select id="{$id}" name="{$name}"  class="{$class}">
	{if $placeholder}
		<option value="">- {translate 'Select'} -</option>
	{/if}
	{foreach $optionList as $itemValue => $itemLabel}
		<option value="{$itemValue|escape}" {if $itemValue==$value}selected="selected"{/if}>
			{block name='label'}
				{if $translate}
					{translate "{$translatePrefix}{$itemLabel}"|escape}
				{else}
					{$itemLabel|escape}
				{/if}
			{/block}
		</option>
	{/foreach}
</select>
{/if}
