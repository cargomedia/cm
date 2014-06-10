{tag el="input" name=$name type="hidden" value="0"}
{tag el="input" type="checkbox" id=$inputId name=$name tabindex=$tabindex value="1" checked=$checked}
<label for="{$inputId}">{block name='label'}{if isset($text)}{$text}{/if}{/block}</label>
