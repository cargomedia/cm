{$showColumns = $optionList|count > 4}
<ul class="{$class} {if $showColumns}columns{/if}">
  {foreach $optionList as $itemValue => $itemLabel}
    <li class="set-item {$name}-value-{$itemValue} {if $showColumns}column4{/if}">
      {if $translate}
        {$label = {translate "{$translatePrefix}{$itemLabel}"|escape}}
      {else}
        {$label = $itemLabel|escape}
      {/if}
      {$checked = $value && in_array($itemValue, $value)}
      {checkbox id="{$inputId}-{$itemValue}" name="{$name}[]" value={$itemValue|escape} checked=$checked label=$label}
    </li>
  {/foreach}
</ul>
