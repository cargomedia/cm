{if !isset($value)}{$value=[]}{/if}
{tag el="input" name=$name id=$id type="text" value=$value|implode:' ' class="textinput {$class}" maxlength=$options.lengthMax tabindex=$tabindex placeholder=$placeholder}
