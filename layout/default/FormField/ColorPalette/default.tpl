<input type="color" name="{$name}" id="{$inputId}" {if $color}value="#{$color->getHexString()}"{/if} readonly />
<ul class="palette">
  {foreach $palette as $paletteColor}
    <li class="setValueFromPalette" data-value="#{$paletteColor->getHexString()}">{$paletteColor->getHexString()}</li>
  {/foreach}
</ul>
