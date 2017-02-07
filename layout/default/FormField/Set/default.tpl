{$showColumns = $optionList|count > 3}
<ul class="set {$class}">
  {foreach $optionList as $itemValue => $itemLabel}
    <li class="set-item {if $showColumns}multiple{/if} {$name}-value-{$itemValue}">
      {if $translate}
        {$label = {{translate "{$translatePrefix}{$itemLabel}"}|escape}}
      {else}
        {$label = $itemLabel|escape}
      {/if}
      {$checked = $value && in_array($itemValue, $value)}
      {checkbox id="{$inputId}-{$itemValue}" name="{$name}[]" value={$itemValue|escape} checked=$checked label=$label}
    </li>
  {/foreach}
</ul>
