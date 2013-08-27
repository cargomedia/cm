{tag el="input" name=$name type="hidden" value="0"}
{tag el="input" id=$id name=$name type="checkbox" tabindex=$tabindex checked=$checked value="1"}
<label for="{$id}">{if isset($text)}{$text}{/if}</label>
