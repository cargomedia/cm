<ul id="{$inputId}" class="palette">
  {foreach $optionList as $itemValue}
    <li class="palette-item">
      <input id="{$inputId}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue}" {if $value && $value->getHexString() == $itemValue}checked{/if} />
      <label for="{$inputId}-{$itemValue}" style="background-color: #{$itemValue}">
        {icon icon='check'}
      </label>
    </li>
  {/foreach}
</ul>
