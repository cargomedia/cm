{$showColumns = $optionList|count > 4}
<ul class="{$class} {if $showColumns}columns{/if}">
  {foreach $optionList as $itemValue => $itemLabel}
    <li class="set-item {$name}-value-{$itemValue} {if $showColumns}column4{/if}">
      {if $translate}
        {$label = {translate "{$translatePrefix}{$itemLabel}"|escape}}
      {else}
        {$label = $itemLabel|escape}
      {/if}
      {$id = "$name-$inputId-$itemValue"}
      {$checked = $value && in_array($itemValue, $value)}
      {checkbox id=$id name={$name|cat: '[]'} value={$itemValue|escape} checked=$checked label=$label}
    </li>
  {/foreach}
</ul>
