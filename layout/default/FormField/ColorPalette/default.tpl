<input type="hidden" name="{$name}" id="{$inputId}" {if $color}value="#{$color->getHexString()}"{/if} readonly />
<ul class="palette">
  {foreach $palette as $paletteColor}
    <li>
      <a href="javascript:;" class="setValueFromPalette palette-item" data-value="#{$paletteColor->getHexString()}" style="background-color: #{$paletteColor->getHexString()}">
        <span class="icon icon-check"></span>
      </a>
    </li>
  {/foreach}
</ul>
