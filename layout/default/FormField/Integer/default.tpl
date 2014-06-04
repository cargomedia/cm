<input type="hidden" class="{$class}" name="{$name}" id="{$inputId}" value="{if isset($value)}{$value}{else}{$options.min}{/if}" />
<div class="noUiSlider-value">{if isset($value)}{$value}{else}{$options.min}{/if}</div>
<div class="noUiSlider-wrapper">
  <div class="noUiSlider"></div>
</div>
