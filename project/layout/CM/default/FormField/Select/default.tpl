{if $display === CM_FormField_Select::DISPLAY_RADIOS}
<ul id="{$id}" class="{$class}">
	{foreach $optionList as $itemValue => $itemLabel}
		<li class="list_label {$name}_value_{$itemValue}" {if $colSize}style="width:{$colSize};"{/if}>
			<label>
				<input name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked="checked"{/if} />
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
<br clear="all" />
{/if}

{if $display === CM_FormField_Select::DISPLAY_SELECT}
<select id="{$id}" name="{$name}"  class="{$class}">
	{if $placeholder}
		<option value="">- {translate 'Select'} -</option>
	{/if}
	{foreach $optionList as $itemValue => $itemLabel}
		<option value="{$itemValue|escape}" {if $itemValue==$value}selected="selected"{/if}>
			{if $translate}
					{translate "{$translatePrefix}{$itemLabel}"|escape}
				{else}
					{$itemLabel|escape}
				{/if}
		</option>
	{/foreach}
</select>
{/if}
