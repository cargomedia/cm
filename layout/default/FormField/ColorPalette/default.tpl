<input type="color" name="{$name}" id="{$inputId}" {if $color}value="#{$color->getHexString()}"{/if} readonly />
<ul class="palette">
  {foreach $palette as $paletteColor}
    <li class="setValueFromPalette palette-item" data-value="#{$paletteColor->getHexString()}" style="background-color: #{$paletteColor->getHexString()}"></li>
  {/foreach}
</ul>
