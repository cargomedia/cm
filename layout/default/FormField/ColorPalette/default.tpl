<ul id="{$inputId}" class="palette">
  {foreach $optionList as $itemValue}
    <li class="palette-item">
      <input id="{$inputId}-{$itemValue}" name="{$name}" type="radio" value="{$itemValue}" {if $value && $value->getHexString() == $itemValue}checked{/if} />
      <label for="{$inputId}-{$itemValue}" style="background-color: #{$itemValue}">
        <span class="icon icon-check"></span>
      </label>
    </li>
  {/foreach}
</ul>
