<ul id="{$id}" class="{$class} clearfix">
{foreach $labelsForValuesSet as $key=>$label}
	<li class="list_label {$name}_value_{$key}"{if isset($colSize)} style="width: {$colSize}"{/if}>
		<label>
			<input type="checkbox" name="{$name}[]" value="{$key}"{if $value && in_array($key,$value)} checked="checked"{/if} />
			<span class="{$name}_label_{$key}">{$label}</span>
		</label>	
	</li>
{/foreach}
</ul>
<div class="clearfix"></div>
