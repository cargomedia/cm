<ul class="palette">
  {foreach $palette as $paletteColor}
    <li class="palette-item">
      <input id="{$inputId}-{$paletteColor->getHexString()}" name="{$name}" type="radio" value="#{$paletteColor->getHexString()}" {if $paletteColor->equals($color)}checked{/if} />
      <label for="{$inputId}-{$paletteColor->getHexString()}" style="background-color: #{$paletteColor->getHexString()}">
        <span class="icon icon-check"></span>
      </label>
    </li>
  {/foreach}
</ul>
