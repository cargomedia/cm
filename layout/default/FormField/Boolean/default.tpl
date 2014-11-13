{tag el="input" name=$name type="hidden" value="0"}
{tag el="input" type="checkbox" id=$inputId name=$name tabindex=$tabindex value="1" checked=$checked}
<label for="{$inputId}">{if $display === CM_FormField_Boolean::DISPLAY_SWITCH}<span class="handle"></span>{/if}{if isset($text)}<span class="label">{$text}</span>{/if}</label>
