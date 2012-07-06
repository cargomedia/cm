{tag el="input" name=$name type="hidden" value="0"}
{tag el="input" id=$id name=$name type="checkbox" tabindex=$tabindex checked=$checked value="1"}
{if isset($text)}<label for="{$id}">{$text}</label>{/if}
