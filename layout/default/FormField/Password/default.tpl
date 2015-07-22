{tag el="input" name=$name id=$inputId type="text" value=$value class="mode-visible textinput {$class}" maxlength=$options.lengthMax tabindex=$tabindex placeholder=$placeholder}
{tag el="input" name=$name id=$inputId type="password" value=$value class="mode-hidden textinput {$class}" maxlength=$options.lengthMax tabindex=$tabindex placeholder=$placeholder}
{button_link class='togglePasswordMask mode-visible' theme='transparent' icon='hide'}
{button_link class='togglePasswordMask mode-hidden' theme='transparent' icon='view'}
