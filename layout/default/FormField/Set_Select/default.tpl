{foreach $optionList as $itemValue => $itemLabel}
	{capture assign='itemHtml'}{strip}
		{block name='label'}{$itemLabel}{/block}
	{/strip}{/capture}
	{$optionList[$itemValue] = $itemHtml}
{/foreach}

{if $display === CM_FormField_Set_Select::DISPLAY_RADIOS}
<ul id="{$id}" class="{$class}">
	{foreach $optionList as $itemValue => $itemLabel}
		<li class="set-item {$name}-value-{$itemValue}">
			<input id="{$id}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked{/if} />
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
{/if}

{if $display === CM_FormField_Set_Select::DISPLAY_SELECT}
	{select id=$id name=$name class=$class optionList=$optionList translate=$translate translatePrefix=$translatePrefix selectedValue=$value placeholder=$placeholder}
{/if}
