{if isset($item)}
	<input name="{$name}" type="radio" value="{$item|escape}" {if $item==$value}checked="checked"{/if} />
{elseif $type == CM_FormField_Select::RADIO}
	<ul id="{$id}" class="{$class}">
		{foreach $valuesAndLabels as $itemValue => $itemLabel}
			<li  class="list_label {$name}_value_{$itemValue}" {if $colSize}style="width:{$colSize};"{/if}>
				<label>
					<input name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked="checked"{/if} />
					<span class="{$name}_label_{$itemValue}">{$itemLabel|escape}</span>
				</label>
			</li>
		{/foreach}
	</ul>
	<br clear="all" />
{else}
	<select id="{$id}" name="{$name}"  class="{$class}">
		{if $invite}
			<option value="">- {translate 'Select'} -</option>
		{/if}
		{foreach $valuesAndLabels as $itemValue => $itemLabel}
			<option value="{$itemValue|escape}" {if $itemValue==$value}selected="selected"{/if}>{$itemLabel|escape}</option>
		{/foreach}
	</select>
{/if}
