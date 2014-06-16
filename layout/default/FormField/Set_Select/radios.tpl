<ul id="{$inputId}" class="{$class}">
  {foreach $optionList as $itemValue => $itemLabel}
    <li class="set-item {$name}-value-{$itemValue}">
      <input id="{$inputId}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue|escape}" {if $itemValue==$value}checked{/if} />
      <label for="{$inputId}-{$itemValue}" class="{$name}-label-{$itemValue}">
        {if $translate}
          {translate "{$translatePrefix}{$itemLabel}"|escape}
        {else}
          {$itemLabel|escape}
        {/if}
      </label>
    </li>
  {/foreach}
</ul>
