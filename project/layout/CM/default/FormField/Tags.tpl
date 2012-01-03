{if !isset($value)}{$value=[]}{/if}
{tag el="textarea" name=$name id=$id type="text" content=$value|implode:' ' class="textinput {$class}" maxlength=$options.lengthMax tabindex=$tabindex placeholder=$placeholder}
